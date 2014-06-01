<?php

namespace Sonos;

class Speaker {

    public $ip;
    public $name;
    public $room;
    protected $cache = [];
    protected $group;
    protected $coordinator;
    protected $uuid;


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


    public function soap($service,$action,$params=[]) {

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


    public function setTopology($attributes) {

        $this->group = (string)$attributes->group;
        $this->coordinator = ($attributes->coordinator == "true");
        $this->uuid = (string)$attributes->uuid;

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


    public function getUuid() {

        if(!$this->uuid) {
            throw new \Exception("Unable to establish the uuid of this speaker");
        }

        return $this->uuid;
    }


}
