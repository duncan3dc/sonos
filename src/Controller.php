<?php

namespace Sonos;

class Controller {

    public $ip;
    public $name;
    public $room;
    protected $cache = [];
    protected static $speakers = false;


    protected function __construct($ip) {

        $this->ip = $ip;

        $xml = $this->curl("/xml/device_description.xml");
        $this->name = (string)$xml->device->friendlyName;
        $this->room = (string)$xml->device->roomName;

    }

    public static function getSpeakerByRoom($room) {

        $speakers = static::getSpeakers();
        foreach($speakers as $controller) {
            if($controller->room == $room) {
                return $controller;
            }
        }

        throw new \Exception("No device found with the room name '" . $room . "'");
    }


    public static function getSpeakersByRoom($room) {

        $return = [];

        $speakers = static::getSpeakers();
        foreach($speakers as $controller) {
            if($controller->room == $room) {
                $return[] = $controller;
            }
        }

        if(count($return) < 1) {
            throw new \Exception("No devices found with the room name '" . $room . "'");
        }

        return $return;
    }


    public static function getSpeakers() {

        if(is_array(static::$speakers)) {
            return static::$speakers;
        }

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
        while(socket_select($read,$write,$except,1) && $read) {
            socket_recvfrom($sock,$tmp,2048,null,$name,$port);
            $response .= $tmp;
        }

        $devices = [];
        foreach(explode("\r\n\r\n",$response) as $reply) {
            if(!$reply) {
                continue;
            }

            $data = array();
            foreach(explode("\r\n", $reply) as $line) {
                if(!$pos = strpos($line,':')) {
                    continue;
                }
                $key = strtolower(substr($line,0,$pos));
                $val = trim(substr($line,$pos+1));
                $data[$key] = $val;
            }
            $devices[] = $data;
        }

        $speakers = [];
        $unique = [];
        foreach($devices as $device) {
            if(in_array($device["usn"],$unique)) {
                continue;
            }
            $url = parse_url($device["location"]);
            $ip = $url["host"];
            $speakers[$ip] = new static($ip);
            $unique[] = $device["usn"];
        }

        return static::$speakers = $speakers;

    }


    public static function getGroups() {

        # Grab any room so that we can request the topology info from it
        $speakers = static::getSpeakers();
        $controller = reset($speakers);

        $topology = $controller->curl("/status/topology");

        $groups = [];
        foreach ($topology->ZonePlayers->ZonePlayer as $player) {
            $player_data = $player->attributes();
            if(!$player_data->coordinator) {
                continue;
            }

            $ip = parse_url($player_data->location)["host"];

            $groups[(string)$player_data->group] = new static($ip);
        }

        return $groups;
    }


    protected function curl($url) {

        if($xml = $this->cache[$url]) {
            return $xml;
        }

        $curl= curl_init();
        curl_setopt_array($curl,[
            CURLOPT_URL             =>  "http://" . $this->ip . ":1400" . $url,
            CURLOPT_RETURNTRANSFER  =>  true,
        ]);
        $response = curl_exec($curl);
        curl_close($curl);

        $xml = simplexml_load_string($response);

        $this->cache[$url] = $xml;

        return $xml;
    }


    protected function soap($service,$action,$params=[]) {

        switch($service) {
            case "AVTransport";
            case "RenderingControl":
                $path = "MediaRenderer";
            break;
            case "ContentDirectory":
                $path = "MediaServer";
            break;
            default:
                throw new \Exception("Unknown service (" . $service . ")");
            break;
        }

        $soap = new \SoapClient(null,[
            "location"  =>  "http://" . $this->ip . ":1400/" . $path . "/" . $service . "/Control",
            "uri"       =>  "urn:schemas-upnp-org:service:" . $service . ":1",
        ]);

        $soapParams = [];
        $params["InstanceID"] = 0;
        foreach($params as $key => $val) {
            $soapParams[] = new \SoapParam(new \SoapVar($val,XSD_STRING),$key);
        }

        return $soap->__soapCall($action,$soapParams);
    }


    public function getState() {
        $data = $this->soap("AVTransport","GetTransportInfo");
        return $data["CurrentTransportState"];
    }


    public function play() {
        return $this->soap("AVTransport","Play",[
            "Speed"         =>  1,
        ]);
    }


    public function pause() {
        return $this->soap("AVTransport","Pause");
    }


    public function next() {
        return $this->soap("AVTransport","Next");
    }

    public function previous() {
        return $this->soap("AVTransport","Previous");
    }


}
