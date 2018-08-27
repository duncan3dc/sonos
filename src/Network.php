<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Devices\Collection;
use duncan3dc\Sonos\Devices\Discovery;
use duncan3dc\Sonos\Devices\Factory;
use duncan3dc\Sonos\Exceptions\NotFoundException;
use duncan3dc\Sonos\Interfaces\AlarmInterface;
use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\Interfaces\Devices\CollectionInterface;
use duncan3dc\Sonos\Interfaces\NetworkInterface;
use duncan3dc\Sonos\Interfaces\PlaylistInterface;
use duncan3dc\Sonos\Interfaces\Services\RadioInterface;
use duncan3dc\Sonos\Interfaces\SpeakerInterface;
use duncan3dc\Sonos\Services\Radio;
use GuzzleHttp\Client;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Provides methods to locate speakers/controllers/playlists on the current network.
 */
final class Network implements NetworkInterface, LoggerAwareInterface
{
    /**
     * @var CollectionInterface $collection The collection of devices on the network.
     */
    private $collection;

    /**
     * @var Speaker[]|null $speakers Speakers that are available on the current network.
     */
    protected $speakers;

    /**
     * @var PlaylistInterface[]|null $playlists Playlists that are available on the current network.
     */
    protected $playlists;

    /**
     * @var AlarmInterface[]|null $alarms Alarms that are available on the current network.
     */
    protected $alarms;


    /**
     * Create a new instance.
     *
     * @param CollectionInterface $collection The collection of devices on this network
     */
    public function __construct(CollectionInterface $collection = null)
    {
        if ($collection === null) {
            $collection = new Discovery();
        }
        $this->collection = $collection;
    }


    /**
     * Set the logger object to use.
     *
     * @var LoggerInterface $logger The logging object
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->collection->setLogger($logger);

        return $this;
    }


    /**
     * Get the logger object to use.
     *
     * @return LoggerInterface $logger The logging object
     */
    public function getLogger()
    {
        return $this->collection->getLogger();
    }


    /**
     * Get all the speakers on the network.
     *
     * @return SpeakerInterface[]
     */
    public function getSpeakers(): array
    {
        if (is_array($this->speakers)) {
            return $this->speakers;
        }

        $devices = $this->collection->getDevices();
        if (count($devices) < 1) {
            throw new \RuntimeException("No devices in this collection");
        }

        $this->getLogger()->info("creating speaker instances");

        $this->speakers = [];
        foreach ($devices as $device) {
            if (!$device->isSpeaker()) {
                continue;
            }

            $speaker = new Speaker($device);

            $this->speakers[$device->getIp()] = $speaker;
        }

        return $this->speakers;
    }


    /**
     * Get a Controller instance from the network.
     *
     * Useful for managing playlists/alarms, as these need a controller but it doesn't matter which one.
     *
     * @return ControllerInterface
     */
    public function getController(): ControllerInterface
    {
        $controllers = $this->getControllers();
        if ($controller = reset($controllers)) {
            return $controller;
        }

        throw new NotFoundException("Unable to find any controllers on the current network");
    }


    /**
     * Get a speaker with the specified room name.
     *
     * @param string $room The name of the room to look for
     *
     * @return SpeakerInterface
     */
    public function getSpeakerByRoom(string $room): SpeakerInterface
    {
        $speakers = $this->getSpeakers();
        foreach ($speakers as $speaker) {
            if ($speaker->getRoom() === $room) {
                return $speaker;
            }
        }

        throw new NotFoundException("Unable to find a speaker for the room '{$room}'");
    }


    /**
     * Get all the speakers with the specified room name.
     *
     * @param string $room The name of the room to look for
     *
     * @return SpeakerInterface[]
     */
    public function getSpeakersByRoom(string $room): array
    {
        $return = [];

        $speakers = $this->getSpeakers();
        foreach ($speakers as $controller) {
            if ($controller->getRoom() === $room) {
                $return[] = $controller;
            }
        }

        return $return;
    }


    /**
     * Get all the coordinators on the network.
     *
     * @return ControllerInterface[]
     */
    public function getControllers(): array
    {
        $controllers = [];

        $speakers = $this->getSpeakers();
        foreach ($speakers as $speaker) {
            if (!$speaker->isCoordinator()) {
                continue;
            }
            $ip = $speaker->getIp();
            $controllers[$ip] = new Controller($speaker, $this);
        }

        return $controllers;
    }


    /**
     * Get the coordinator for the specified room name.
     *
     * @param string $room The name of the room to look for
     *
     * @return ControllerInterface
     */
    public function getControllerByRoom(string $room): ControllerInterface
    {
        $group = $this->getSpeakerByRoom($room)->getGroup();

        $controllers = $this->getControllers();
        foreach ($controllers as $controller) {
            if ($controller->getGroup() === $group) {
                return $controller;
            }
        }

        throw new NotFoundException("Unable to find the controller for the room '{$room}'");
    }


    /**
     * Get the coordinator for the specified ip address.
     *
     * @param string $ip The ip address of the speaker
     *
     * @return ControllerInterface
     */
    public function getControllerByIp(string $ip): ControllerInterface
    {
        $speakers = $this->getSpeakers();
        if (!array_key_exists($ip, $speakers)) {
            throw new NotFoundException("Unable to find a speaker for the IP address '{$ip}'");
        }

        $group = $speakers[$ip]->getGroup();

        foreach ($this->getControllers() as $controller) {
            if ($controller->getGroup() === $group) {
                return $controller;
            }
        }
    }


    /**
     * Get all the playlists available on the network.
     *
     * @return PlaylistInterface[]
     */
    public function getPlaylists(): array
    {
        if (is_array($this->playlists)) {
            return $this->playlists;
        }

        $controller = $this->getController();
        $data = $controller->soap("ContentDirectory", "Browse", [
            "ObjectID"          =>  "SQ:",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  100,
            "SortCriteria"      =>  "",
        ]);
        $parser = new XmlParser($data["Result"]);

        $playlists = [];
        foreach ($parser->getTags("container") as $container) {
            $playlists[] = new Playlist($container, $controller);
        }

        return $this->playlists = $playlists;
    }


    /**
     * Check if a playlist with the specified name exists on this network.
     *
     * If no case-sensitive match is found it will return a case-insensitive match.
     *
     * @param string $name The name of the playlist
     *
     * @return bool
     */
    public function hasPlaylist(string $name): bool
    {
        $playlists = $this->getPlaylists();
        foreach ($playlists as $playlist) {
            if ($playlist->getName() === $name) {
                return true;
            }
            if (strtolower($playlist->getName()) === strtolower($name)) {
                return true;
            }
        }

        return false;
    }


    /**
     * Get the playlist with the specified name.
     *
     * If no case-sensitive match is found it will return a case-insensitive match.
     *
     * @param string $name The name of the playlist
     *
     * @return PlaylistInterface
     */
    public function getPlaylistByName(string $name): PlaylistInterface
    {
        $roughMatch = false;

        $playlists = $this->getPlaylists();
        foreach ($playlists as $playlist) {
            if ($playlist->getName() === $name) {
                return $playlist;
            }
            if (strtolower($playlist->getName()) === strtolower($name)) {
                $roughMatch = $playlist;
            }
        }

        if ($roughMatch) {
            return $roughMatch;
        }

        throw new NotFoundException("No playlist called '{$name}' exists on this network");
    }


    /**
     * Get the playlist with the specified id.
     *
     * @param string $id The ID of the playlist (eg SQ:123)
     *
     * @return PlaylistInterface
     */
    public function getPlaylistById(string $id): PlaylistInterface
    {
        $controller = $this->getController();

        return new Playlist($id, $controller);
    }


    /**
     * Create a new playlist.
     *
     * @param string $name The name to give to the playlist
     *
     * @return PlaylistInterface
     */
    public function createPlaylist(string $name): PlaylistInterface
    {
        $controller = $this->getController();

        $data = $controller->soap("AVTransport", "CreateSavedQueue", [
            "Title"                 =>  $name,
            "EnqueuedURI"           =>  "",
            "EnqueuedURIMetaData"   =>  "",
        ]);

        $playlist = new Playlist($data["AssignedObjectID"], $controller);

        $this->playlists[] = $playlist;

        return $playlist;
    }


    /**
     * Get all the alarms available on the network.
     *
     * @return AlarmInterface[]
     */
    public function getAlarms(): array
    {
        if (is_array($this->alarms)) {
            return $this->alarms;
        }

        $data = $this->getController()->soap("AlarmClock", "ListAlarms");
        $parser = new XmlParser($data["CurrentAlarmList"]);

        $alarms = [];
        foreach ($parser->getTags("Alarm") as $tag) {
            $alarms[] = new Alarm($tag, $this);
        }

        return $this->alarms = $alarms;
    }


    /**
     * Get the alarm from the specified id.
     *
     * @param int $id The ID of the alarm
     *
     * @return AlarmInterface
     */
    public function getAlarmById(int $id): AlarmInterface
    {
        $alarms = $this->getAlarms();
        foreach ($alarms as $alarm) {
            if ($alarm->getId() === $id) {
                return $alarm;
            }
        }

        throw new NotFoundException("Unable to find an alarm with the id {$id} on this network");
    }


    /**
     * Get a Radio instance for the network.
     *
     * @return RadioInterface
     */
    public function getRadio(): RadioInterface
    {
        $controller = $this->getController();
        return new Radio($controller);
    }
}
