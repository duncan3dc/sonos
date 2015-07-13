<?php

namespace duncan3dc\Sonos;

use Doctrine\Common\Cache\Cache as CacheInterface;
use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Services\Radio;
use duncan3dc\Sonos\Tracks\Stream;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Provides methods to locate speakers/controllers/playlists on the current network.
 */
class Network implements LoggerAwareInterface
{
    /**
     * @var Speaker[]|null $speakers Speakers that are available on the current network.
     */
    protected $speakers;

    /**
     * @var Playlists[]|null $playlists Playlists that are available on the current network.
     */
    protected $playlists;

    /**
     * @var Alarm[]|null $alarms Alarms that are available on the current network.
     */
    protected $alarms;

    /**
     * @var CacheInterface $cache The cache object to use for the expensive multicast discover to find Sonos devices on the network.
     */
    protected $cache;

    /**
     * @var LoggerInterface $logger The logging object.
     */
    protected $logger;

    /**
     * @var string $broadcastIp ip being used to send the broadcast to
     */
    protected $broadcastIp = '239.255.255.250';

    /**
     * Create a new instance.
     *
     * @param CacheInterface $cache The cache object to use for the expensive multicast discover to find Sonos devices on the network
     * @param LoggerInterface $logger The logging object
     * @param string $broadcastIp ip being used to send the broadcast to
     */
    public function __construct(CacheInterface $cache = null, LoggerInterface $logger = null)
    {
        if ($cache === null) {
            $cache = new Cache;
        }
        $this->cache = $cache;

        if ($logger === null) {
            $logger = new NullLogger;
        }
        $this->logger = $logger;
    }


    /**
     * Set the IP address being used for broadcasting
     *
     * @var string $broadcastIp ip of broadcast
     *
     * @return static
     */
    public function setBroadcastIp($broadcastIp)
    {
        $this->broadcastIp = $broadcastIp;

        return $this;
    }

    /**
     * Set the logger object to use.
     *
     * @var LoggerInterface $logger The logging object
     *
     * @return static
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }


    /**
     * Get the logger object to use.
     *
     * @return LoggerInterface $logger The logging object
     */
    public function getLogger()
    {
        return $this->logger;
    }


    /**
     * Get all the devices on the current network.
     *
     * @return string[] An array of ip addresses
     */
    protected function getDevices()
    {
        $this->logger->info("discovering devices...");

        $ip = $this->broadcastIp;
        $port = 1900;

        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_set_option($sock, getprotobyname("ip"), IP_MULTICAST_TTL, 2);

        $data = "M-SEARCH * HTTP/1.1\r\n";
        $data .= "HOST: " . $ip . ":reservedSSDPport\r\n";
        $data .= "MAN: ssdp:discover\r\n";
        $data .= "MX: 1\r\n";
        $data .= "ST: urn:schemas-upnp-org:device:ZonePlayer:1\r\n";

        $this->logger->debug($data);

        socket_sendto($sock, $data, strlen($data), null, $ip, $port);

        $read = [$sock];
        $write = [];
        $except = [];
        $name = null;
        $port = null;
        $tmp = "";

        $response = "";
        while (socket_select($read, $write, $except, 1)) {
            socket_recvfrom($sock, $tmp, 2048, null, $name, $port);
            $response .= $tmp;
        }

        $this->logger->debug($response);

        $devices = [];
        foreach (explode("\r\n\r\n", $response) as $reply) {
            if (!$reply) {
                continue;
            }

            $data = [];
            foreach (explode("\r\n", $reply) as $line) {
                if (!$pos = strpos($line, ":")) {
                    continue;
                }
                $key = strtolower(substr($line, 0, $pos));
                $val = trim(substr($line, $pos + 1));
                $data[$key] = $val;
            }
            $devices[] = $data;
        }

        $return = [];
        $unique = [];
        foreach ($devices as $device) {
            if ($device["st"] !== "urn:schemas-upnp-org:device:ZonePlayer:1") {
                continue;
            }
            if (in_array($device["usn"], $unique)) {
                continue;
            }
            $this->logger->info("found device: {usn}", $device);

            $url = parse_url($device["location"]);
            $ip = $url["host"];

            $return[] = $ip;
            $unique[] = $device["usn"];
        }

        return $return;
    }


    /**
     * Get all the speakers on the network.
     *
     * @return Speaker[]
     */
    public function getSpeakers()
    {
        if (is_array($this->speakers)) {
            return $this->speakers;
        }

        $this->logger->info("creating speaker instances");

        if ($this->cache->contains("devices")) {
            $this->logger->info("getting device info from cache");
            $devices = $this->cache->fetch("devices");
        } else {
            $devices = $this->getDevices();

            # Only cache the devices if we actually found some
            if (count($devices) > 0) {
                $this->cache->save("devices", $devices);
            }
        }

        if (count($devices) < 1) {
            throw new \Exception("No devices found on the current network");
        }

        $speakers = [];
        foreach ($devices as $ip) {
            $device = new Device($ip, $this->cache, $this->logger);
            if ($device->isSpeaker()) {
                $speakers[$ip] = new Speaker($device);
            }
        }

        return $this->speakers = $speakers;
    }


    /**
     * Reset any previously gathered speaker information.
     *
     * @return static
     */
    public function clearTopology()
    {
        $this->speakers = null;

        return $this;
    }


    /**
     * Get a Controller instance from the network.
     *
     * Useful for managing playlists/alarms, as these need a controller but it doesn't matter which one.
     *
     * @return Controller|null
     */
    public function getController()
    {
        $controllers = $this->getControllers();
        if ($controller = reset($controllers)) {
            return $controller;
        }
    }


    /**
     * Get a speaker with the specified room name.
     *
     * @param string $room The name of the room to look for
     *
     * @return Speaker|null
     */
    public function getSpeakerByRoom($room)
    {
        $speakers = $this->getSpeakers();
        foreach ($speakers as $speaker) {
            if ($speaker->room === $room) {
                return $speaker;
            }
        }
    }


    /**
     * Get all the speakers with the specified room name.
     *
     * @param string $room The name of the room to look for
     *
     * @return Speaker[]
     */
    public function getSpeakersByRoom($room)
    {
        $return = [];

        $speakers = $this->getSpeakers();
        foreach ($speakers as $controller) {
            if ($controller->room === $room) {
                $return[] = $controller;
            }
        }

        return $return;
    }


    /**
     * Get all the coordinators on the network.
     *
     * @return Controller[]
     */
    public function getControllers()
    {
        $controllers = [];

        $speakers = $this->getSpeakers();
        foreach ($speakers as $speaker) {
            if (!$speaker->isCoordinator()) {
                continue;
            }
            $controllers[$speaker->ip] = new Controller($speaker, $this);
        }

        return $controllers;
    }


    /**
     * Get the coordinator for the specified room name.
     *
     * @param string $room The name of the room to look for
     *
     * @return Controller|null
     */
    public function getControllerByRoom($room)
    {
        if (!$speaker = $this->getSpeakerByRoom($room)) {
            return;
        }

        $group = $speaker->getGroup();

        $controllers = $this->getControllers();
        foreach ($controllers as $controller) {
            if ($controller->getGroup() === $group) {
                return $controller;
            }
        }
    }


    /**
     * Get the coordinator for the specified ip address.
     *
     * @param string $ip The ip address of the speaker
     *
     * @return Controller|null
     */
    public function getControllerByIp($ip)
    {
        $speakers = $this->getSpeakers();
        if (!array_key_exists($ip, $speakers)) {
            throw new \InvalidArgumentException("No speaker found for the IP address '" . $ip . "'");
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
     * @return Playlist[]
     */
    public function getPlaylists()
    {
        if (is_array($this->playlists)) {
            return $this->playlists;
        }

        $controller = $this->getController();
        if ($controller === null) {
            throw new \RuntimeException("No controller found on the current network");
        }

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
     * @param string The name of the playlist
     *
     * @return bool
     */
    public function hasPlaylist($name)
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
     * @param string The name of the playlist
     *
     * @return Playlist|null
     */
    public function getPlaylistByName($name)
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
    }


    /**
     * Get the playlist with the specified id.
     *
     * @param int The ID of the playlist
     *
     * @return Playlist
     */
    public function getPlaylistById($id)
    {
        $controller = $this->getController();
        if ($controller === null) {
            throw new \RuntimeException("No controller found on the current network");
        }

        return new Playlist($id, $controller);
    }


    /**
     * Create a new playlist.
     *
     * @param string The name to give to the playlist
     *
     * @return Playlist
     */
    public function createPlaylist($name)
    {
        $controller = $this->getController();
        if ($controller === null) {
            throw new \RuntimeException("No controller found on the current network");
        }

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
     * @return Alarm[]
     */
    public function getAlarms()
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
     * Get alarms for the specified id.
     *
     * @return Alarm
     */
    public function getAlarmById($id)
    {
        $id = (int) $id;

        $alarms = $this->getAlarms();
        foreach ($alarms as $alarm) {
            if ($alarm->getId() === $id) {
                return $alarm;
            }
        }
    }


    /**
     * Get a Radio instance for the network.
     *
     * @return Radio
     */
    public function getRadio()
    {
        $controller = $this->getController();
        return new Radio($controller);
    }


    /**
     * Get the favourite radio stations.
     *
     * @return Stream[]
     */
    public function getRadioStations()
    {
        trigger_error("The getRadioStations() method is deprecated in favour of getRadio()->getFavouriteStations()", \E_USER_DEPRECATED);
        return $this->getRadio()->getFavouriteStations();
    }


    /**
     * Get the favourite radio shows.
     *
     * @return Stream[]
     */
    public function getRadioShows()
    {
        trigger_error("The getRadioShows() method is deprecated in favour of getRadio()->getFavouriteShows()", \E_USER_DEPRECATED);
        return $this->getRadio()->getFavouriteShows();
    }
}
