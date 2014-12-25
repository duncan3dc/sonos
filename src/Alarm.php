<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlElement;

/**
 * Provides an interface for managing the alarms on the network.
 */
class Alarm
{
    const ONCE = 0;
    const MONDAY = 1;
    const TUESDAY = 2;
    const WEDNESDAY = 4;
    const THURSDAY = 8;
    const FRIDAY = 16;
    const SATURDAY = 32;
    const SUNDAY = 64;
    const EVERYDAY = 127;

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
        return (int) $this->id;
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
     * Get the frequency of the alarm.
     *
     * The result is an integer which can be compared using the bitwise operators and the class constants for each day.
     * If the alarm is a one time only alarm then it will not match any of the day constants, but will be equal to the class constant ONCE.
     *
     * @return int
     */
    public function getFrequency()
    {
        $data = $this->attributes["Recurrence"];
        if ($data === "ONCE") {
            return self::ONCE;
        }
        if ($data === "DAILY") {
            $data = "ON_0123456";
        } elseif ($data === "WEEKDAYS") {
            $data = "ON_01234";
        } elseif ($data === "WEEKENDS") {
            $data = "ON_56";
        }
        if (!preg_match("/^ON_([0-9]+)$/", $data, $matches)) {
            throw new \RuntimeException("Unrecognised frequency for alarm (" . $data . "), please report this issue at github.com/duncan3dc/sonos/issues");
        }

        $data = $matches[1];
        $days = 0;
        $tests = [
            "0" =>  self::MONDAY,
            "1" =>  self::TUESDAY,
            "2" =>  self::WEDNESDAY,
            "3" =>  self::THURSDAY,
            "4" =>  self::FRIDAY,
            "5" =>  self::SATURDAY,
            "6" =>  self::SUNDAY,
        ];
        foreach ($tests as $key => $val) {
            if (strpos($data, (string) $key) !== false) {
                $days = $days | $val;
            }
        }

        return $days;
    }


    /**
     * Check whether this alarm is active on a particular day.
     *
     * @param int $day Which day to check/set
     *
     * @return boolean
     */
    protected function onHandler($day)
    {
        $frequency = $this->getFrequency();
        return (bool) ($frequency & $day);
    }


    /**
     * Check whether this alarm is active on mondays.
     *
     * @return boolean
     */
    public function onMonday()
    {
        return $this->onHandler(self::MONDAY);
    }


    /**
     * Check whether this alarm is active on tuesdays.
     *
     * @return boolean
     */
    public function onTuesday()
    {
        return $this->onHandler(self::TUESDAY);
    }


    /**
     * Check whether this alarm is active on wednesdays.
     *
     * @return boolean
     */
    public function onWednesday()
    {
        return $this->onHandler(self::WEDNESDAY);
    }


    /**
     * Check whether this alarm is active on thursdays.
     *
     * @return boolean
     */
    public function onThursday()
    {
        return $this->onHandler(self::THURSDAY);
    }


    /**
     * Check whether this alarm is active on fridays.
     *
     * @return boolean
     */
    public function onFriday()
    {
        return $this->onHandler(self::FRIDAY);
    }


    /**
     * Check whether this alarm is active on saturdays.
     *
     * @return boolean
     */
    public function onSaturday()
    {
        return $this->onHandler(self::SATURDAY);
    }


    /**
     * Check whether this alarm is active on sundays.
     *
     * @return boolean
     */
    public function onSunday()
    {
        return $this->onHandler(self::SUNDAY);
    }


    /**
     * Check whether this alarm is a one time only alarm.
     *
     * @return boolean
     */
    public function once()
    {
        return $this->getFrequency() === self::ONCE;
    }


    /**
     * Check whether this alarm runs every day or not.
     *
     * @return boolean
     */
    public function everyday()
    {
        return $this->getFrequency() === self::EVERYDAY;
    }


    /**
     * Get the frequency of the alarm as a human readable description.
     *
     * @return string
     */
    public function getFrequencyDescription()
    {
        $data = $this->attributes["Recurrence"];
        if ($data === "ONCE") {
            return "Once";
        }
        if ($data === "DAILY") {
            return "Every Day";
        }
        if ($data === "WEEKDAYS") {
            return "Weekdays";
        }
        if ($data === "WEEKENDS") {
            return "Weekends";
        }

        $data = $this->getFrequency();
        $days = [
            self::MONDAY    =>  "Mon",
            self::TUESDAY   =>  "Tues",
            self::WEDNESDAY =>  "Wed",
            self::THURSDAY  =>  "Thurs",
            self::FRIDAY    =>  "Fri",
            self::SATURDAY  =>  "Sat",
            self::SUNDAY    =>  "Sun",
        ];
        $description = "";
        foreach ($days as $key => $val) {
            if ($data & $key) {
                if (strlen($description) > 0) {
                    $description .= ",";
                }
                $description .= $val;
            }
        }
        return $description;
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
