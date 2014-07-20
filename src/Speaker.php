<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;

class Speaker
{
    public $ip;
    public $name;
    public $room;
    protected $cache = [];
    protected $group;
    protected $coordinator;
    protected $uuid;
    protected $topology;


    public function __construct($ip)
    {
        $this->ip = $ip;

        $parser = $this->getXml("/xml/device_description.xml");
        $device = $parser->getTag("device");
        $this->name = $device->getTag("friendlyName")->nodeValue;
        $this->room = $device->getTag("roomName")->nodeValue;
    }


    public function getXml($url)
    {
        if($xml = $this->cache[$url]) {
            return $xml;
        }

        $parser = new XmlParser("http://" . $this->ip . ":1400" . $url);

        $this->cache[$url] = $parser;

        return $parser;
    }


    public function soap($service, $action, $params = [])
    {
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
        }

        $soap = new \SoapClient(null, [
            "location"  =>  "http://" . $this->ip . ":1400/" . $path . "/" . $service . "/Control",
            "uri"       =>  "urn:schemas-upnp-org:service:" . $service . ":1",
        ]);

        $soapParams = [];
        $params["InstanceID"] = 0;
        foreach($params as $key => $val) {
            $soapParams[] = new \SoapParam(new \SoapVar($val, XSD_STRING), $key);
        }

        return $soap->__soapCall($action, $soapParams);
    }


    protected function getTopology()
    {
        if($this->topology) {
            return true;
        }

        $topology = $this->getXml("/status/topology");
        $players = $topology->getTag("ZonePlayers")->getTags("ZonePlayer");
        foreach($players as $player) {
            $attributes = $player->getAttributes();
            $ip = parse_url($attributes["location"])["host"];
            if($ip == $this->ip) {
                return $this->setTopology($attributes);
            }
        }

        throw new \Exception("Failed to lookup the topology info for this speaker");
    }


    public function setTopology($attributes)
    {
        $this->topology = true;
        $this->group = $attributes["group"];
        $this->coordinator = ($attributes["coordinator"] == "true");
        $this->uuid = $attributes["uuid"];
    }


    public function getGroup()
    {
        $this->getTopology();
        return $this->group;
    }


    public function isCoordinator()
    {
        $this->getTopology();
        return $this->coordinator;
    }


    public function getUuid()
    {
        $this->getTopology();
        return $this->uuid;
    }


    public function getVolume()
    {
        return $this->soap("RenderingControl", "GetVolume", [
            "Channel"   =>  "Master",
        ]);
    }


    public function setVolume($volume)
    {
        return $this->soap("RenderingControl", "SetVolume", [
            "Channel"       =>  "Master",
            "DesiredVolume" =>  $volume,
        ]);
    }


    public function adjustVolume($adjust)
    {
        return $this->soap("RenderingControl", "SetRelativeVolume", [
            "Channel"       =>  "Master",
            "Adjustment"    =>  $adjust,
        ]);
    }
}
