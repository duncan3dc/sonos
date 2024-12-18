<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlElement;
use duncan3dc\Sonos\Interfaces\AlarmInterface;
use duncan3dc\Sonos\Interfaces\NetworkInterface;
use duncan3dc\Sonos\Interfaces\SpeakerInterface;
use duncan3dc\Sonos\Interfaces\UriInterface;
use duncan3dc\Sonos\Interfaces\Utils\TimeInterface;
use duncan3dc\Sonos\Tracks\Factory;
use duncan3dc\Sonos\Utils\Time;

/**
 * Provides an interface for managing the alarms on the network.
 */
final class Alarm implements AlarmInterface
{
    /**
     * @var array<int, int> $days An mapping of php day values to our day constants.
     */
    protected $days = [
        0 =>  AlarmInterface::SUNDAY,
        1 =>  AlarmInterface::MONDAY,
        2 =>  AlarmInterface::TUESDAY,
        3 =>  AlarmInterface::WEDNESDAY,
        4 =>  AlarmInterface::THURSDAY,
        5 =>  AlarmInterface::FRIDAY,
        6 =>  AlarmInterface::SATURDAY,
    ];

    /**
     * @var string $id The unique id of the alarm
     */
    protected $id;

    /**
     * @var array<string, mixed> $attributes The attributes of the alarm
     */
    protected $attributes;

    /**
     * @var NetworkInterface $network A Network instance this alarm is from.
     */
    protected $network;

    /**
     * Create an instance of the Alarm class.
     *
     * @param XmlElement $xml The xml element with the relevant attributes
     * @param NetworkInterface $network A Network instance this alarm is from
     */
    public function __construct(XmlElement $xml, NetworkInterface $network)
    {
        $this->id = $xml->getAttribute("ID");
        $this->attributes = $xml->getAttributes();
        $this->network = $network;
    }


    /**
     * Send a soap request to the speaker for this alarm.
     *
     * @param string $service The service to send the request to
     * @param string $action The action to call
     * @param array<string, string|int|bool> $params The parameters to pass
     *
     * @return mixed
     */
    protected function soap(string $service, string $action, array $params = [])
    {
        $params["ID"] = $this->id;

        return $this->getSpeaker()->soap($service, $action, $params);
    }


    /**
     * Get the id of the alarm.
     *
     * @return int
     */
    public function getId(): int
    {
        return (int) $this->id;
    }


    /**
     * Get the room of the alarm.
     *
     * @return string
     */
    public function getRoom(): string
    {
        return $this->attributes["RoomUUID"];
    }


    /**
     * Set the room of the alarm.
     *
     * @param string $uuid The unique id of the room (eg, RINCON_B8E93758723601400)
     *
     * @return $this
     */
    public function setRoom(string $uuid): AlarmInterface
    {
        $this->attributes["RoomUUID"] = $uuid;

        return $this->save();
    }


    /**
     * Get the speaker of the alarm.
     *
     * @return SpeakerInterface
     */
    public function getSpeaker(): SpeakerInterface
    {
        foreach ($this->network->getSpeakers() as $speaker) {
            if ($speaker->getUuid() === $this->getRoom()) {
                return $speaker;
            }
        }

        throw new \RuntimeException("Unable to find a speaker for this alarm");
    }


    /**
     * Set the speaker of the alarm.
     *
     * @param SpeakerInterface $speaker The speaker to attach this alarm to
     *
     * @return $this
     */
    public function setSpeaker(SpeakerInterface $speaker): AlarmInterface
    {
        return $this->setRoom($speaker->getUuid());
    }


    /**
     * Get the start time of the alarm.
     *
     * @return TimeInterface
     */
    public function getTime(): TimeInterface
    {
        return Time::parse($this->attributes["StartTime"]);
    }


    /**
     * Set the start time of the alarm.
     *
     * @param TimeInterface $time The time to set the alarm for
     *
     * @return $this
     */
    public function setTime(TimeInterface $time): AlarmInterface
    {
        $this->attributes["StartTime"] = $time->asString();

        return $this->save();
    }


    /**
     * Get the duration of the alarm.
     *
     * @return TimeInterface
     */
    public function getDuration(): TimeInterface
    {
        return Time::parse($this->attributes["Duration"]);
    }


    /**
     * Set the duration of the alarm.
     *
     * @param TimeInterface $duration The duration of the alarm
     *
     * @return $this
     */
    public function setDuration(TimeInterface $duration): AlarmInterface
    {
        $this->attributes["Duration"] = $duration->asString();

        return $this->save();
    }


    /**
     * Get the frequency of the alarm.
     *
     * The result is an integer which can be compared using the bitwise operators and the class constants for each day.
     * If the alarm is a one time only alarm then it will not match any of the day constants,
     * but will be equal to the class constant ONCE.
     *
     * @return int
     */
    public function getFrequency(): int
    {
        $data = $this->attributes["Recurrence"];
        if ($data === "ONCE") {
            return AlarmInterface::ONCE;
        }
        if ($data === "DAILY") {
            $data = "ON_0123456";
        } elseif ($data === "WEEKDAYS") {
            $data = "ON_12345";
        } elseif ($data === "WEEKENDS") {
            $data = "ON_06";
        }
        if (!preg_match("/^ON_([0-9]+)$/", $data, $matches)) {
            $error = "Unrecognised frequency for alarm ({$data}), please report this issue on github.com";
            throw new \RuntimeException($error);
        }

        $data = $matches[1];
        $days = 0;
        foreach ($this->days as $key => $val) {
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
     * @return $this
     */
    public function setFrequency(int $frequency): AlarmInterface
    {
        $recurrence = "ON_";
        foreach ($this->days as $key => $val) {
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

        return $this->save();
    }


    /**
     * Check or set whether this alarm is active on a particular day.
     *
     * @param int $day Which day to check/set
     * @param ?bool $set Set this alarm to be active or not on the specified day
     *
     * @return bool|AlarmInterface Returns true/false when checking, or AlarmInterface when setting
     */
    protected function onHandler(int $day, ?bool $set = null)
    {
        $frequency = $this->getFrequency();
        if ($set === null) {
            return (bool) ($frequency & $day);
        }
        if ($set && $frequency ^ $day) {
            return $this->setFrequency($frequency | $day);
        }
        if (!$set && $frequency & $day) {
            return $this->setFrequency($frequency ^ $day);
        }

        return $this;
    }


    /**
     * Check or set whether this alarm is active on mondays.
     *
     * @param ?bool $set Set this alarm to be active or not on mondays
     *
     * @return bool|AlarmInterface Returns true/false when checking, or AlarmInterface when setting
     */
    public function onMonday(?bool $set = null)
    {
        return $this->onHandler(AlarmInterface::MONDAY, $set);
    }


    /**
     * Check or set whether this alarm is active on tuesdays.
     *
     * @param ?bool $set Set this alarm to be active or not on tuesdays
     *
     * @return bool|AlarmInterface Returns true/false when checking, or AlarmInterface when setting
     */
    public function onTuesday(?bool $set = null)
    {
        return $this->onHandler(AlarmInterface::TUESDAY, $set);
    }


    /**
     * Check or set whether this alarm is active on wednesdays.
     *
     * @param ?bool $set Set this alarm to be active or not on wednesdays
     *
     * @return bool|AlarmInterface Returns true/false when checking, or AlarmInterface when setting
     */
    public function onWednesday(?bool $set = null)
    {
        return $this->onHandler(AlarmInterface::WEDNESDAY, $set);
    }


    /**
     * Check or set whether this alarm is active on thursdays.
     *
     * @param ?bool $set Set this alarm to be active or not on thursdays
     *
     * @return bool|AlarmInterface Returns true/false when checking, or AlarmInterface when setting
     */
    public function onThursday(?bool $set = null)
    {
        return $this->onHandler(AlarmInterface::THURSDAY, $set);
    }


    /**
     * Check or set whether this alarm is active on fridays.
     *
     * @param ?bool $set Set this alarm to be active or not on fridays
     *
     * @return bool|AlarmInterface Returns true/false when checking, or AlarmInterface when setting
     */
    public function onFriday(?bool $set = null)
    {
        return $this->onHandler(AlarmInterface::FRIDAY, $set);
    }


    /**
     * Check or set whether this alarm is active on saturdays.
     *
     * @param ?bool $set Set this alarm to be active or not on saturdays
     *
     * @return bool|AlarmInterface Returns true/false when checking, or AlarmInterface when setting
     */
    public function onSaturday(?bool $set = null)
    {
        return $this->onHandler(AlarmInterface::SATURDAY, $set);
    }


    /**
     * Check or set whether this alarm is active on sundays.
     *
     * @param ?bool $set Set this alarm to be active or not on sundays
     *
     * @return bool|AlarmInterface Returns true/false when checking, or AlarmInterface when setting
     */
    public function onSunday(?bool $set = null)
    {
        return $this->onHandler(AlarmInterface::SUNDAY, $set);
    }


    /**
     * Check or set whether this alarm is a one time only alarm.
     *
     * @param ?bool $set Set this alarm to be a one time only alarm
     *
     * @return bool|AlarmInterface Returns true/false when checking, or AlarmInterface when setting
     */
    public function once(?bool $set = null)
    {
        if ($set) {
            return $this->setFrequency(AlarmInterface::ONCE);
        }
        return $this->getFrequency() === AlarmInterface::ONCE;
    }


    /**
     * Check or set whether this alarm runs every day or not.
     *
     * @param ?bool $set Set this alarm to be active every day
     *
     * @return bool|AlarmInterface Returns true/false when checking, or AlarmInterface when setting
     */
    public function daily(?bool $set = null)
    {
        if ($set) {
            return $this->setFrequency(AlarmInterface::DAILY);
        }
        return $this->getFrequency() === AlarmInterface::DAILY;
    }


    /**
     * Get the frequency of the alarm as a human readable description.
     *
     * @return string
     */
    public function getFrequencyDescription(): string
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
            AlarmInterface::MONDAY    =>  "Mon",
            AlarmInterface::TUESDAY   =>  "Tues",
            AlarmInterface::WEDNESDAY =>  "Wed",
            AlarmInterface::THURSDAY  =>  "Thurs",
            AlarmInterface::FRIDAY    =>  "Fri",
            AlarmInterface::SATURDAY  =>  "Sat",
            AlarmInterface::SUNDAY    =>  "Sun",
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
     * @inheritdoc
     */
    public function getMusic(): UriInterface
    {
        return new Uri($this->attributes["ProgramURI"], $this->attributes["ProgramMetaData"]);
    }


    /**
     * @inheritdoc
     */
    public function setMusic(UriInterface $uri): AlarmInterface
    {
        $this->attributes["ProgramURI"] = $uri->getUri();
        $this->attributes["ProgramMetaData"] = $uri->getMetaData();

        return $this->save();
    }


    /**
     * Get the volume of the alarm.
     *
     * @return int
     */
    public function getVolume(): int
    {
        return (int) $this->attributes["Volume"];
    }


    /**
     * Set the volume of the alarm.
     *
     * @param int $volume The volume of the alarm
     *
     * @return $this
     */
    public function setVolume(int $volume): AlarmInterface
    {
        $this->attributes["Volume"] = $volume;

        return $this->save();
    }


    /**
     * Get a particular PlayMode.
     *
     * @param string $type The play mode attribute to get
     *
     * @return bool
     */
    protected function getPlayMode(string $type): bool
    {
        $mode = Helper::getMode($this->attributes["PlayMode"]);
        return $mode[$type];
    }


    /**
     * Set a particular PlayMode.
     *
     * @param string $type The play mode attribute to update
     * @param bool $value The value to set the attribute to
     *
     * @return $this
     */
    protected function setPlayMode(string $type, bool $value): AlarmInterface
    {
        $mode = Helper::getMode($this->attributes["PlayMode"]);
        if ($mode[$type] === $value) {
            return $this;
        }

        $mode[$type] = $value;
        $this->attributes["PlayMode"] = Helper::setMode($mode);

        return $this->save();
    }


    /**
     * Check if repeat is active.
     *
     * @return bool
     */
    public function getRepeat(): bool
    {
        return $this->getPlayMode("repeat");
    }


    /**
     * Turn repeat mode on or off.
     *
     * @param bool $repeat Whether repeat should be on or not
     *
     * @return $this
     */
    public function setRepeat(bool $repeat): AlarmInterface
    {
        return $this->setPlayMode("repeat", $repeat);
    }


    /**
     * Check if shuffle is active.
     *
     * @return bool
     */
    public function getShuffle(): bool
    {
        return $this->getPlayMode("shuffle");
    }


    /**
     * Turn shuffle mode on or off.
     *
     * @param bool $shuffle Whether shuffle should be on or not
     *
     * @return $this
     */
    public function setShuffle(bool $shuffle): AlarmInterface
    {
        return $this->setPlayMode("shuffle", $shuffle);
    }


    /**
     * Check if the alarm is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->attributes["Enabled"] ? true : false;
    }


    /**
     * Make the alarm active.
     *
     * @return $this
     */
    public function activate(): AlarmInterface
    {
        $this->attributes["Enabled"] = true;

        return $this->save();
    }


    /**
     * Make the alarm inactive.
     *
     * @return $this
     */
    public function deactivate(): AlarmInterface
    {
        $this->attributes["Enabled"] = false;

        return $this->save();
    }


    /**
     * Delete this alarm.
     *
     * @return void
     */
    public function delete()
    {
        $this->soap("AlarmClock", "DestroyAlarm");
        unset($this->id);
    }


    /**
     * Update the alarm with the current instance settings.
     *
     * @return $this
     */
    protected function save(): AlarmInterface
    {
        $params = [
            "StartLocalTime"        =>  $this->attributes["StartTime"],
            "Duration"              =>  $this->attributes["Duration"],
            "Recurrence"            =>  $this->attributes["Recurrence"],
            "Enabled"               =>  $this->attributes["Enabled"] ? "1" : "0",
            "RoomUUID"              =>  $this->attributes["RoomUUID"],
            "ProgramURI"            =>  $this->attributes["ProgramURI"],
            "ProgramMetaData"       =>  $this->attributes["ProgramMetaData"],
            "PlayMode"              =>  $this->attributes["PlayMode"],
            "Volume"                =>  $this->attributes["Volume"],
            "IncludeLinkedZones"    =>  $this->attributes["IncludeLinkedZones"],
        ];

        $this->soap("AlarmClock", "UpdateAlarm", $params);

        return $this;
    }
}
