<?php

namespace Sonos;

class Speaker {

    public $ip;
    public $name;
    public $room;
    protected $cache = [];
    protected $group;
    protected $coordinator;


    public function __construct($ip) {

        $this->ip = $ip;

        $xml = $this->curl("/xml/device_description.xml");
        $this->name = (string)$xml->device->friendlyName;
        $this->room = (string)$xml->device->roomName;

    }


    public function curl($url) {

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


    public function setTopology($attributes) {

        $this->group = (string)$attributes->group;
        $this->coordinator = ($attributes->coordinator == "true");

    }


    public function getGroup() {

        if(!$this->group) {
            throw new \Exception("Unable to establish the group of this speaker");
        }

        return $this->group;
    }


    public function isCoordinator() {
        return $this->coordinator;
    }


}
