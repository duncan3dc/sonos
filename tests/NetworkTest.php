<?php

namespace duncan3dc\SonosTests;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\ObjectIntruder\Intruder;
use duncan3dc\Sonos\Alarm;
use duncan3dc\Sonos\Controller;
use duncan3dc\Sonos\Exceptions\NotFoundException;
use duncan3dc\Sonos\Interfaces\AlarmInterface;
use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\Interfaces\Devices\CollectionInterface;
use duncan3dc\Sonos\Interfaces\PlaylistInterface;
use duncan3dc\Sonos\Interfaces\Services\RadioInterface;
use duncan3dc\Sonos\Interfaces\SpeakerInterface;
use duncan3dc\Sonos\Network;
use duncan3dc\Sonos\Playlist;
use duncan3dc\Sonos\Services\Radio;
use Mockery;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class NetworkTest extends MockTest
{
    /** @var Network */
    protected $network;

    /** @var CollectionInterface|MockInterface */
    private $collection;


    public function setUp()
    {
        $this->collection = Mockery::mock(CollectionInterface::class);
        $this->network = new Network($this->collection);
    }


    public function tearDown()
    {
        Mockery::close();
    }


    public function testSetLogger()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $this->collection->shouldReceive("setLogger")->with($logger)->once();
        $this->assertSame($this->network, $this->network->setLogger($logger));
    }


    public function testGetLogger()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $this->collection->shouldReceive("getLogger")->with()->once()->andReturn($logger);
        $this->assertSame($logger, $this->network->getLogger());
    }


    private function mockSpeakers()
    {
        $speakers = [];
        foreach (range(1, 3) as $id) {
            $speaker = Mockery::mock(SpeakerInterface::class);
            $speaker->shouldReceive("getIp")->andReturn("127.0.0.{$id}");
            $speakers[] = $speaker;
        }

        $network = new Intruder($this->network);
        $network->speakers = $speakers;

        return $speakers;
    }


    public function testGetSpeakers()
    {
        $this->mockSpeakers();
        $speakers = $this->network->getSpeakers();

        $this->assertSame(3, count($speakers));
        $this->assertContainsOnlyInstancesOf(SpeakerInterface::class, $speakers);
    }


    public function testExcludePairedSpeakers()
    {
        $devices = [];

        $setup = [
            "192.168.0.1" => "SPEAKER_A:1",
            "192.168.0.2" => "",
            "192.168.0.3" => "SPEAKER_B:1",
        ];
        foreach ($setup as $ip => $group) {
            $device = $this->getDevice($ip);
            $device->shouldReceive("isSpeaker")->with()->andReturn(true);
            $device->shouldReceive("soap")
                ->with("ZoneGroupTopology", "GetZoneGroupAttributes", [])
                ->andReturn([
                    "CurrentZoneGroupID" => $group,
                    "CurrentZonePlayerUUIDsInGroup" => "",
                ]);

            $devices[] = $device;
        }

        $this->collection->shouldReceive("getDevices")->with()->andReturn($devices);

        $this->collection->shouldReceive("getLogger")->with()->andReturn(new NullLogger());
        $speakers = $this->network->getSpeakers();

        $this->assertSame(2, count($speakers));
    }


    public function testGetControllers()
    {
        $speakers = $this->mockSpeakers();

        $speakers[0]->shouldReceive("isCoordinator")->with()->andReturn(true);
        $speakers[1]->shouldReceive("isCoordinator")->with()->andReturn(false);
        $speakers[2]->shouldReceive("isCoordinator")->with()->andReturn(true);

        $controllers = $this->network->getControllers();
        $this->assertSame(2, count($controllers));
        $this->assertContainsOnlyInstancesOf(ControllerInterface::class, $controllers);

        $this->assertSame("127.0.0.1", reset($controllers)->getIp());
        $this->assertSame("127.0.0.3", end($controllers)->getIp());
    }


    public function testGetController1()
    {
        $speakers = $this->mockSpeakers();

        $speakers[0]->shouldReceive("isCoordinator")->with()->andReturn(false);
        $speakers[1]->shouldReceive("isCoordinator")->with()->andReturn(false);
        $speakers[2]->shouldReceive("isCoordinator")->with()->andReturn(true);

        $controller = $this->network->getController();
        $this->assertSame("127.0.0.3", $controller->getIp());
    }
    public function testGetController2()
    {
        $speakers = $this->mockSpeakers();

        $speakers[0]->shouldReceive("isCoordinator")->with()->andReturn(false);
        $speakers[1]->shouldReceive("isCoordinator")->with()->andReturn(false);
        $speakers[2]->shouldReceive("isCoordinator")->with()->andReturn(false);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Unable to find any controllers on the current network");
        $this->network->getController();
    }


    public function testGetSpeakerByRoom1()
    {
        $speakers = $this->mockSpeakers();

        $speakers[0]->shouldReceive("getRoom")->once()->with()->andReturn("Living Room");
        $speakers[1]->shouldReceive("getRoom")->once()->with()->andReturn("Bathroom");

        $speaker = $this->network->getSpeakerByRoom("Bathroom");
        $this->assertSame("127.0.0.2", $speaker->getIp());
    }
    public function testGetSpeakerByRoom2()
    {
        $speakers = $this->mockSpeakers();

        $speakers[0]->shouldReceive("getRoom")->once()->with()->andReturn("Living Room");
        $speakers[1]->shouldReceive("getRoom")->once()->with()->andReturn("Bathroom");
        $speakers[2]->shouldReceive("getRoom")->once()->with()->andReturn("Office");

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Unable to find a speaker for the room 'Kitchen'");
        $this->network->getSpeakerByRoom("Kitchen");
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
            throw new NotFoundException("Unable to find the speaker for the IP address '{$ip}'");
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
     * @param string The name of the playlist
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
     * @param string The ID of the playlist (eg SQ:123)
     *
     * @return PlaylistInterface
     */
    public function getPlaylistById(string $id): PlaylistInterface
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
     * @return PlaylistInterface
     */
    public function createPlaylist(string $name): PlaylistInterface
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
        $id = (int) $id;

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
