<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlElement;

/**
 * Provides an interface for managing the alarms on the network.
 */
class Alarm
{
    /**
     * @var string $id The unique id of the alarm
     */
    protected $id;

    /**
     * @var array $attributes The attributes of the alarm
     */
    protected $attributes;

    /**
     * Create an instance of the Alarm class.
     *
     * @param int|XmlElement $param The id of the alarm, or an xml element with the relevant attributes
     */
    public function __construct(XmlElement $param)
    {
        $this->id = $param->getAttribute("ID");
        $this->attributes = $param->getAttributes();
    }


    /**
     * Get the id of the alarm.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Get the start time of the alarm.
     *
     * @return string
     */
    public function getTime()
    {
        return $this->attributes["StartTime"];
    }


    /**
     * Get the duration of the alarm.
     *
     * @return string
     */
    public function getDuration()
    {
        return $this->attributes["Duration"];
    }


    /**
     * Get the recurrence of the alarm.
     *
     * @return string
     */
    public function getRecurrence()
    {
        return $this->attributes["Recurrence"];
    }


    /**
     * Get the volume of the alarm.
     *
     * @return int
     */
    public function getVolume()
    {
        return (int) $this->attributes["Volume"];
    }


    /**
     * Check if repeat is active.
     *
     * @return boolean
     */
    public function getRepeat()
    {
        $mode = Helper::getMode($this->attributes["PlayMode"]);
        return $mode["repeat"];
    }


    /**
     * Check if shuffle is active.
     *
     * @return boolean
     */
    public function getShuffle()
    {
        $mode = Helper::getMode($this->attributes["PlayMode"]);
        return $mode["shuffle"];
    }


    /**
     * Check if the alarm is active.
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->attributes["Enabled"] ? true : false;
    }
}
