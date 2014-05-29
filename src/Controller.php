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


}
