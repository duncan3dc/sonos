<?php

namespace duncan3dc\Sonos;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use duncan3dc\DomParser\XmlParser;

/**
 * Provides methods to locate speakers/controllers/playlists on the current network.
 */
class Network
{
    /**
     * @var Speaker[] $speakers Speakers that are available on the current network.
     */
    protected $speakers;

    /**
     * @var Playlists[] $playlists Playlists that are available on the current network.
     */
    protected $playlists;

    /**
     * @var Alarm[] $alarms Alarms that are available on the current network.
     */
    protected $alarms;

    /**
     * @var Cache $cache The cache object to use for the expensive multicast discover to find Sonos devices on the network
     */
    protected $cache;


    public function __construct(Cache $cache = null)
    {
        if ($cache === null) {
            $cache = new FilesystemCache(sys_get_temp_dir() . DIRECTORY_SEPARATOR . "sonos");
        }
        $this->cache = $cache;
    }


    /**
     * Get all the devices on the current network.
     *
     * @return string[] An array of ip addresses
     */
    protected function getDevices()
    {
        $ip = "239.255.255.250";
        $port = 1900;

        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        socket_set_option($sock, getprotobyname("ip"), IP_MULTICAST_TTL, 2);

        $data = "M-SEARCH * HTTP/1.1\r\n";
        $data .= "HOST: " . $ip . ":reservedSSDPport\r\n";
        $data .= "MAN: ssdp:discover\r\n";
        $data .= "MX: 1\r\n";
        $data .= "ST: urn:schemas-upnp-org:device:ZonePlayer:1\r\n";

        socket_sendto($sock, $data, strlen($data), null, $ip, $port);

        $read = [$sock];
        $write = [];
        $except = [];
        $name = null;
        $port = null;
        $tmp = "";

        $response = "";
        while (socket_select($read, $write, $except, 1) && $read) {
            socket_recvfrom($sock, $tmp, 2048, null, $name, $port);
            $response .= $tmp;
        }

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
            if (in_array($device["usn"], $unique)) {
                continue;
            }
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

        if ($this->cache->contains("devices")) {
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
            $speakers[$ip] = new Speaker($ip);
        }

        $speaker = reset($speakers);
        $topology = $speaker->getXml("/status/topology");
        $players = $topology->getTag("ZonePlayers")->getTags("ZonePlayer");
        foreach ($players as $player) {
            $attributes = $player->getAttributes();
            $ip = parse_url($attributes["location"])["host"];
            if (array_key_exists($ip, $speakers)) {
                $speakers[$ip]->setTopology($attributes);
            }
        }

        return $this->speakers = $speakers;
    }


    public function clearTopology()
    {
        $this->speakers = null;
    }


    /**
     * Get a Controller instance from the network.
     *
     * Useful for managing playlists/alarms, as these need a controller but it doesn't matter which one.
     *
     * @return Controller
     */
    public function getController()
    {
        $controllers = $this->getControllers();
        return reset($controllers);
    }


    /**
     * Get a speaker with the specified room name.
     *
     * @param string $room The name of the room to look for
     *
     * @return Speaker
     */
    public function getSpeakerByRoom($room)
    {
        $speakers = $this->getSpeakers();
        foreach ($speakers as $speaker) {
            if ($speaker->room === $room) {
                return $speaker;
            }
        }

        throw new \InvalidArgumentException("No speaker found with the room name '" . $room . "'");
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

        if (count($return) < 1) {
            throw new \InvalidArgumentException("No speakers found with the room name '" . $room . "'");
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
     * @return Controller[]
     */
    public function getControllerByRoom($room)
    {
        $speaker = $this->getSpeakerByRoom($room);
        $group = $speaker->getGroup();

        $controllers = $this->getControllers();
        foreach ($controllers as $controller) {
            if ($controller->getGroup() === $group) {
                return $controller;
            }
        }

        throw new \InvalidArgumentException("No controller found with the room name '" . $room . "'");
    }


    /**
     * Get the coordinator for the specified ip address.
     *
     * @param string $ip The ip address of the speaker
     *
     * @return Controller
     */
    public function getControllerByIp($ip)
    {
        $speaker = new Speaker($ip);
        $group = $speaker->getGroup();

        foreach ($this->getControllers() as $controller) {
            if ($controller->getGroup() === $group) {
                return $controller;
            }
        }

        throw new \InvalidArgumentException("No controller found for the IP address '" . $room . "'");
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
     * Get the playlist with the specified name.
     *
     * If no case-sensitive match is found it will return a case-insensitive match.
     *
     * @param string The name of the playlist
     *
     * @return Playlist
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

        throw new \InvalidArgumentException("No playlist found with the name '" . $name . "'");
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
        return new Playlist($id, $this->getController());
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

        $controller = $this->getController();

        $data = $controller->soap("AlarmClock", "ListAlarms");
        $parser = new XmlParser($data["CurrentAlarmList"]);

        $alarms = [];
        foreach ($parser->getTags("Alarm") as $tag) {
            $alarms[] = new Alarm($tag, $controller);
        }

        return $this->alarms = $alarms;
    }


    public function getAlarmById($id)
    {
        $id = (integer) $id;

        $alarms = $this->getAlarms();
        foreach ($alarms as $alarm) {
            if ($alarm->getId() === $id) {
                return $alarm;
            }
        }

        throw new \InvalidArgumentException("No alarm found with the ID " . $id);
    }
}
