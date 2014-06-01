<?php

namespace Sonos;
use \duncan3dc\DomParser\XmlParser;

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

        $parser = $this->getXml("/xml/device_description.xml");
        $device = $parser->getTag("device");
        $this->name = $device->getTag("friendlyName")->nodeValue;
        $this->room = $device->getTag("roomName")->nodeValue;

    }


    public function getXml($url) {

        if($xml = $this->cache[$url]) {
            return $xml;
        }

        $parser = new XmlParser("http://" . $this->ip . ":1400" . $url);

        $this->cache[$url] = $parser;

        return $parser;
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

        $this->group = $attributes["group"];
        $this->coordinator = ($attributes["coordinator"] == "true");
        $this->uuid = $attributes["uuid"];

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


    public function getVolume() {
        return $this->soap("RenderingControl","GetVolume",array(
            "Channel"   =>  "Master",
        ));
    }


    public function setVolume($volume) {
        return $this->soap("RenderingControl","SetVolume",array(
            "Channel"       =>  "Master",
            "DesiredVolume" =>  $volume,
        ));
    }


    public function adjustVolume($adjust) {
        return $this->soap("RenderingControl","SetRelativeVolume",array(
            "Channel"       =>  "Master",
            "Adjustment"    =>  $adjust,
        ));
    }


}
