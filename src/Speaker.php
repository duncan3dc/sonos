<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;

/**
 * Provides an interface to individual speakers that is mostly read-only, although the volume can be set using this class.
 */
class Speaker
{
    /**
     * @var string $ip The IP address of the speaker.
     */
    public $ip;

    /**
     * @var string $name The "Friendly" name reported by the speaker.
     */
    public $name;

    /**
     * @var string $room The room name assigned to this speaker.
     */
    public $room;

    /**
     * @var array $cache Cached data to increase performance.
     */
    protected $cache = [];

    /**
     * @var string $group The group id this speaker is a part of.
     */
    protected $group;

    /**
     * @var boolean $coordinator Whether this speaker is the coordinator of it's current group.
     */
    protected $coordinator;

    /**
     * @var string $uuid The unique id of this speaker.
     */
    protected $uuid;

    /**
     * @var boolean $topology A flag to indicate whether we have gathered the topology for this speaker or not.
     */
    protected $topology;


    /**
     * Create an instance of the Speaker class.
     *
     * @param string $ip The ip address that the controller is listening on
     */
    public function __construct($ip)
    {
        $this->ip = $ip;

        $parser = $this->getXml("/xml/device_description.xml");
        $device = $parser->getTag("device");
        $this->name = $device->getTag("friendlyName")->nodeValue;
        $this->room = $device->getTag("roomName")->nodeValue;
    }


    /**
     * Retrieve some xml from the controller.
     * _This method is intended for internal use only._
     *
     * @param string $url The url to retrieve
     *
     * @return XmlParser
     */
    public function getXml($url)
    {
        if (!isset($this->cache[$url])) {
            $this->cache[$url] = new XmlParser("http://" . $this->ip . ":1400" . $url);
        }

        return $this->cache[$url];
    }


    /**
     * Send a soap request to the speaker.
     * _This method is intended for internal use only_.
     *
     * @param string $service The service to send the request to
     * @param string $action The action to call
     * @param array $params The parameters to pass
     *
     * @return mixed
     */
    public function soap($service, $action, $params = [])
    {
        switch ($service) {
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
        foreach ($params as $key => $val) {
            $soapParams[] = new \SoapParam(new \SoapVar($val, XSD_STRING), $key);
        }

        return $soap->__soapCall($action, $soapParams);
    }


    /**
     * Get the attributes needed for the classes instance variables.
     * _This method is intended for internal use only_.
     *
     * @return void
     */
    protected function getTopology()
    {
        if ($this->topology) {
            return;
        }

        $topology = $this->getXml("/status/topology");
        $players = $topology->getTag("ZonePlayers")->getTags("ZonePlayer");
        foreach ($players as $player) {
            $attributes = $player->getAttributes();
            $ip = parse_url($attributes["location"])["host"];
            if ($ip == $this->ip) {
                $this->setTopology($attributes);
                return;
            }
        }

        throw new \Exception("Failed to lookup the topology info for this speaker");
    }


    /**
     * Set the instance variables based on the xml attributes.
     * _This method is intended for internal use only_.
     *
     * @param array $attributes The attributes from the xml that represent this speaker
     *
     * @return void
     */
    public function setTopology(array $attributes)
    {
        $this->topology = true;
        $this->group = $attributes["group"];
        $this->coordinator = ($attributes["coordinator"] == "true");
        $this->uuid = $attributes["uuid"];
    }


    /**
     * Get the uuid of the group this speaker is a member of.
     *
     * @return string
     */
    public function getGroup()
    {
        $this->getTopology();
        return $this->group;
    }


    /**
     * Check if this speaker is the coordinator of it's current group.
     *
     * @return boolean
     */
    public function isCoordinator()
    {
        $this->getTopology();
        return $this->coordinator;
    }


    /**
     * Get the uuid of this speaker.
     *
     * @return string The uuid of this speaker
     */
    public function getUuid()
    {
        $this->getTopology();
        return $this->uuid;
    }


    /**
     * Get the current volume of this speaker.
     *
     * @param int The current volume between 0 and 100
     *
     * @return int
     */
    public function getVolume()
    {
        return $this->soap("RenderingControl", "GetVolume", [
            "Channel"   =>  "Master",
        ]);
    }


    /**
     * Adjust the volume of this speaker to a specific value.
     *
     * @param int $volume The amount to set the volume to between 0 and 100
     *
     * @return void
     */
    public function setVolume($volume)
    {
        return $this->soap("RenderingControl", "SetVolume", [
            "Channel"       =>  "Master",
            "DesiredVolume" =>  $volume,
        ]);
    }


    /**
     * Adjust the volume of this speaker by a relative amount.
     *
     * @param int $adjust The amount to adjust by between -100 and 100
     *
     * @return void
     */
    public function adjustVolume($adjust)
    {
        return $this->soap("RenderingControl", "SetRelativeVolume", [
            "Channel"       =>  "Master",
            "Adjustment"    =>  $adjust,
        ]);
    }
}
