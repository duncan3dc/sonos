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
    const DAILY = 127;

    /**
     * @var string $id The unique id of the alarm
     */
    protected $id;

    /**
     * @var array $attributes The attributes of the alarm
     */
    protected $attributes;

    /**
     * @var Controller $controller A Controller instance this alarm can be reached via
     */
    protected $controller;

    /**
     * Create an instance of the Alarm class.
     *
     * @param XmlElement $xml The xml element with the relevant attributes
     * @param Controller $controller A Controller instance this alarm can be reached via
     */
    public function __construct(XmlElement $xml, Controller $controller)
    {
        $this->id = $xml->getAttribute("ID");
        $this->attributes = $xml->getAttributes();
        $this->controller = $controller;
    }


    /**
     * Send a soap request to the controller for this alarm.
     *
     * @param string $service The service to send the request to
     * @param string $action The action to call
     * @param array $params The parameters to pass
     *
     * @return mixed
     */
    protected function soap($service, $action, $params = [])
    {
        $params["ID"] = $this->id;

        return $this->controller->soap($service, $action, $params);
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
        list($hours, $minutes) = explode(":", $this->attributes["StartTime"]);
        return sprintf("%02s:%02s", $hours, $minutes);
    }


    /**
     * Set the start time of the alarm.
     *
     * @param string $time The time to set the alarm for (hh:mm)
     *
     * @return void
     */
    public function setTime($time)
    {
        $exception = new \InvalidArgumentException("Invalid time specified, time must be in the format hh:mm");
        if (!preg_match("/^([0-9]{1,2}):([0-9]{1,2})$/", $time, $matches)) {
            throw $exception;
        }
        $hours = $matches[1];
        $minutes = $matches[2];

        if ($hours > 23 || $minutes > 59) {
            throw $exception;
        }

        $this->attributes["StartTime"] = sprintf("%02s:%02s:%02s", $hours, $minutes, 0);
        $this->save();
    }


    /**
     * Get the duration of the alarm.
     *
     * @return int The duration in minutes
     */
    public function getDuration()
    {
        list($hours, $minutes) = explode(":", $this->attributes["Duration"]);
        return ($hours * 60) + $minutes;
    }


    /**
     * Set the duration of the alarm.
     *
     * @param int The duration in minutes
     *
     * @return void
     */
    public function setDuration($duration)
    {
        $hours = floor($duration / 60);
        $minutes = $duration % 60;

        $this->attributes["Duration"] = sprintf("%02s:%02s:%02s", $hours, $minutes, 0);
        $this->save();
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
     * Set the frequency of the alarm.
     *
     * @param int $frequency The integer representing the frequency (using the bitwise class constants)
     *
     * @return void
     */
    public function setFrequency($frequency)
    {
        $recurrence = "ON_";
        $days = [
            "0" =>  self::MONDAY,
            "1" =>  self::TUESDAY,
            "2" =>  self::WEDNESDAY,
            "3" =>  self::THURSDAY,
            "4" =>  self::FRIDAY,
            "5" =>  self::SATURDAY,
            "6" =>  self::SUNDAY,
        ];
        foreach ($days as $key => $val) {
            if ($frequency & $val) {
                $recurrence .= $key;
            }
        }

        if ($recurrence === "ON_") {
            $recurrence = "ONCE";
        } elseif ($recurrence === "ON_0123456") {
            $recurrence = "DAILY";
        } elseif ($recurrence === "ON_01234") {
            $recurrence = "WEEKDAYS";
        } elseif ($recurrence === "ON_56") {
            $recurrence = "WEEKENDS";
        }

        $this->attributes["Recurrence"] = $recurrence;
        $this->save();
    }


    /**
     * Check or set whether this alarm is active on a particular day.
     *
     * @param int $day Which day to check/set
     * @param boolean $set Set this alarm to be active or not on the specified day
     *
     * @return boolean|void Returns true/false when checking, or void when setting
     */
    protected function onHandler($day, $set = null)
    {
        $frequency = $this->getFrequency();
        if ($set === null) {
            return (bool) ($frequency & $day);
        }
        if ($set && $frequency ^ $day) {
            $this->setFrequency($frequency | $day);
        }
        if (!$set && $frequency & $day) {
            $this->setFrequency($frequency ^ $day);
        }
    }


    /**
     * Check or set whether this alarm is active on mondays.
     *
     * @param boolean $set Set this alarm to be active or not on mondays
     *
     * @return boolean|void Returns true/false when checking, or void when setting
     */
    public function onMonday($set = null)
    {
        return $this->onHandler(self::MONDAY, $set);
    }


    /**
     * Check or set whether this alarm is active on tuesdays.
     *
     * @param boolean $set Set this alarm to be active or not on tuesdays
     *
     * @return boolean|void Returns true/false when checking, or void when setting
     */
    public function onTuesday($set = null)
    {
        return $this->onHandler(self::TUESDAY, $set);
    }


    /**
     * Check or set whether this alarm is active on wednesdays.
     *
     * @param boolean $set Set this alarm to be active or not on wednesdays
     *
     * @return boolean|void Returns true/false when checking, or void when setting
     */
    public function onWednesday($set = null)
    {
        return $this->onHandler(self::WEDNESDAY, $set);
    }


    /**
     * Check or set whether this alarm is active on thursdays.
     *
     * @param boolean $set Set this alarm to be active or not on thursdays
     *
     * @return boolean|void Returns true/false when checking, or void when setting
     */
    public function onThursday($set = null)
    {
        return $this->onHandler(self::THURSDAY, $set);
    }


    /**
     * Check or set whether this alarm is active on fridays.
     *
     * @param boolean $set Set this alarm to be active or not on fridays
     *
     * @return boolean|void Returns true/false when checking, or void when setting
     */
    public function onFriday($set = null)
    {
        return $this->onHandler(self::FRIDAY, $set);
    }


    /**
     * Check or set whether this alarm is active on saturdays.
     *
     * @param boolean $set Set this alarm to be active or not on saturdays
     *
     * @return boolean|void Returns true/false when checking, or void when setting
     */
    public function onSaturday($set = null)
    {
        return $this->onHandler(self::SATURDAY, $set);
    }


    /**
     * Check or set whether this alarm is active on sundays.
     *
     * @param boolean $set Set this alarm to be active or not on sundays
     *
     * @return boolean|void Returns true/false when checking, or void when setting
     */
    public function onSunday($set = null)
    {
        return $this->onHandler(self::SUNDAY, $set);
    }


    /**
     * Check or set whether this alarm is a one time only alarm.
     *
     * @param boolean $set Set this alarm to be a one time only alarm
     *
     * @return boolean|void Returns true/false when checking, or void when setting
     */
    public function once($set = null)
    {
        if ($set) {
            $this->setFrequency(self::ONCE);
        }
        return $this->getFrequency() === self::ONCE;
    }


    /**
     * Check or set whether this alarm runs every day or not.
     *
     * @param boolean $set Set this alarm to be active every day
     *
     * @return boolean|void Returns true/false when checking, or void when setting
     */
    public function daily($set = null)
    {
        if ($set) {
            $this->setFrequency(self::DAILY);
        }
        return $this->getFrequency() === self::DAILY;
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
            return "Daily";
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
     * Set the volume of the alarm.
     *
     * @param int $volume The volume of the alarm
     *
     * @return void
     */
    public function setVolume($volume)
    {
        $this->attributes["Volume"] = $volume;
        $this->save();
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
     * Turn repeat mode on or off.
     *
     * @param boolean $repeat Whether repeat should be on or not
     *
     * @return void
     */
    public function setRepeat($repeat)
    {
        $repeat = (boolean) $repeat;

        $mode = Helper::getMode($this->attributes["PlayMode"]);
        if ($mode["repeat"] === $repeat) {
            return;
        }

        $mode["repeat"] = $repeat;
        $this->attributes["PlayMode"] = Helper::setMode($mode);
        $this->save();
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
     * Turn shuffle mode on or off.
     *
     * @param boolean $repeat Whether repeat should be on or not
     *
     * @return void
     */
    public function setShuffle($shuffle)
    {
        $shuffle = (boolean) $shuffle;

        $mode = Helper::getMode($this->attributes["PlayMode"]);
        if ($mode["shuffle"] === $shuffle) {
            return;
        }

        $mode["shuffle"] = $shuffle;
        $this->attributes["PlayMode"] = Helper::setMode($mode);
        $this->save();
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


    /**
     * Make the alarm active.
     *
     * @return void
     */
    public function activate()
    {
        $this->attributes["Enabled"] = true;
        $this->save();
    }


    /**
     * Make the alarm inactive.
     *
     * @return void
     */
    public function deactivate()
    {
        $this->attributes["Enabled"] = false;
        $this->save();
    }


    /**
     * Delete this alarm.
     *
     * @return void
     */
    public function delete()
    {
        $this->soap("AlarmClock", "DestroyAlarm");
        $this->id = null;
    }


    /**
     * Update the alarm with the current instance settings.
     *
     * @return void
     */
    protected function save()
    {
        $params = [
            "StartLocalTime"        =>  $this->attributes["StartTime"],
            "Duration"              =>  $this->attributes["Duration"],
            "Recurrence"            =>  $this->attributes["Recurrence"],
            "Enabled"               =>  $this->attributes["Enabled"],
            "RoomUUID"              =>  $this->attributes["RoomUUID"],
            "ProgramURI"            =>  $this->attributes["ProgramURI"],
            "ProgramMetaData"       =>  $this->attributes["ProgramMetaData"],
            "PlayMode"              =>  $this->attributes["PlayMode"],
            "Volume"                =>  $this->attributes["Volume"],
            "IncludeLinkedZones"    =>  $this->attributes["IncludeLinkedZones"],
        ];

        $this->soap("AlarmClock", "UpdateAlarm", $params);
    }
}
