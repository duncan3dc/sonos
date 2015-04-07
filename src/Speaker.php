<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;
use Psr\Log\LoggerInterface;

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
     * @var Device $device The instance of the Device class to send requests to.
     */
    protected $device;

    /**
     * @var string $name The "Friendly" name reported by the speaker.
     */
    public $name;

    /**
     * @var string $room The room name assigned to this speaker.
     */
    public $room;

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
     * @param Device|string $param An Device instance or the ip address that the speaker is listening on
     * @param LoggerInterface $logger A logging object
     */
    public function __construct($param, LoggerInterface $logger = null)
    {
        if ($param instanceof Device) {
            $this->device = $param;
            $this->ip = $this->device->ip;
        } else {
            $this->ip = $param;
            $this->device = new Device($this->ip, $logger);
        }

        $parser = $this->device->getXml("/xml/device_description.xml");
        $device = $parser->getTag("device");
        $this->name = (string) $device->getTag("friendlyName");
        $this->room = (string) $device->getTag("roomName");
        $this->model = (string) $device->getTag("modelNumber");

        if (!$this->device->isSpeaker()) {
            throw new \InvalidArgumentException("You cannot create a Speaker instance for this model: {$this->model}");
        }
    }


    /**
     * Send a soap request to the speaker.
     *
     * @param string $service The service to send the request to
     * @param string $action The action to call
     * @param array $params The parameters to pass
     *
     * @return mixed
     */
    public function soap($service, $action, array $params = [])
    {
        return $this->device->soap($service, $action, $params);
    }


    /**
     * Get the attributes needed for the classes instance variables.
     *
     * _This method is intended for internal use only_.
     *
     * @return void
     */
    protected function getTopology()
    {
        if ($this->topology) {
            return;
        }

        $topology = $this->device->getXml("/status/topology");
        $players = $topology->getTag("ZonePlayers")->getTags("ZonePlayer");
        foreach ($players as $player) {
            $attributes = $player->getAttributes();
            $ip = parse_url($attributes["location"])["host"];

            if ($ip === $this->ip) {
                $this->topology = true;
                $this->group = $attributes["group"];
                $this->coordinator = ($attributes["coordinator"] === "true");
                $this->uuid = $attributes["uuid"];
                return;
            }
        }

        throw new \Exception("Failed to lookup the topology info for this speaker");
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
        return (int) $this->soap("RenderingControl", "GetVolume", [
            "Channel"   =>  "Master",
        ]);
    }


    /**
     * Adjust the volume of this speaker to a specific value.
     *
     * @param int $volume The amount to set the volume to between 0 and 100
     *
     * @return static
     */
    public function setVolume($volume)
    {
        $this->soap("RenderingControl", "SetVolume", [
            "Channel"       =>  "Master",
            "DesiredVolume" =>  $volume,
        ]);

        return $this;
    }


    /**
     * Adjust the volume of this speaker by a relative amount.
     *
     * @param int $adjust The amount to adjust by between -100 and 100
     *
     * @return static
     */
    public function adjustVolume($adjust)
    {
        $this->soap("RenderingControl", "SetRelativeVolume", [
            "Channel"       =>  "Master",
            "Adjustment"    =>  $adjust,
        ]);

        return $this;
    }


    /**
     * Check if this speaker is currently muted.
     *
     * @return boolean
     */
    public function isMuted()
    {
        return $this->soap("RenderingControl", "GetMute", [
            "Channel"   =>  "Master",
        ]);
    }


    /**
     * Mute this speaker.
     *
     * @return static
     */
    public function mute()
    {
        $this->soap("RenderingControl", "SetMute", [
            "Channel"       =>  "Master",
            "DesiredMute"   =>  1,
        ]);

        return $this;
    }


    /**
     * Unmute this speaker.
     *
     * @return static
     */
    public function unmute()
    {
        $this->soap("RenderingControl", "SetMute", [
            "Channel"       =>  "Master",
            "DesiredMute"   =>  0,
        ]);

        return $this;
    }


    /**
     * Turn the white LED on
     *
     * @return static
     */
    public function setLedOn()
    {
        $this->soap("DeviceProperties", "SetLEDState", [
            "Channel"       =>  "Master",
            "DesiredLEDState" =>  'On',
        ]);

        return $this;
    }


    /**
     * Turn the white LED off
     *
     * @return static
     */
    public function setLedOff()
    {
        $this->soap("DeviceProperties", "SetLEDState", [
            "Channel"       =>  "Master",
            "DesiredLEDState" =>  'Off',
        ]);

        return $this;
    }


    /**
     * Get the LED status of this speaker
     *
     * @return boolean
     */
    public function isLedOn()
    {
        return $this->soap("DeviceProperties", "GetLEDState", [
            "Channel"   =>  "Master",
        ]) == 'On' ? true : false;
    }
}
