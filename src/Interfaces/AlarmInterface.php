<?php

namespace duncan3dc\Sonos\Interfaces;

use duncan3dc\Sonos\Interfaces\SpeakerInterface;
use duncan3dc\Sonos\Interfaces\Utils\TimeInterface;
use duncan3dc\Sonos\Utils\Time;

/**
 * Provides an interface for managing the alarms on the network.
 */
interface AlarmInterface
{
    const ONCE      =   0;
    const MONDAY    =   1;
    const TUESDAY   =   self::MONDAY    * 2;
    const WEDNESDAY =   self::TUESDAY   * 2;
    const THURSDAY  =   self::WEDNESDAY * 2;
    const FRIDAY    =   self::THURSDAY  * 2;
    const SATURDAY  =   self::FRIDAY    * 2;
    const SUNDAY    =   self::SATURDAY  * 2;
    const DAILY     =   (self::SUNDAY   * 2) - 1;


    /**
     * Get the id of the alarm.
     *
     * @return int
     */
    public function getId(): int;


    /**
     * Get the room of the alarm.
     *
     * @return string
     */
    public function getRoom(): string;


    /**
     * Set the room of the alarm.
     *
     * @param string $uuid The unique id of the room (eg, RINCON_B8E93758723601400)
     *
     * @return AlarmInterface
     */
    public function setRoom(string $uuid): AlarmInterface;


    /**
     * Get the speaker of the alarm.
     *
     * @return SpeakerInterface
     */
    public function getSpeaker(): SpeakerInterface;


    /**
     * Set the speaker of the alarm.
     *
     * @param SpeakerInterface $speaker The speaker to attach this alarm to
     *
     * @return AlarmInterface
     */
    public function setSpeaker(SpeakerInterface $speaker): AlarmInterface;


    /**
     * Get the start time of the alarm.
     *
     * @return TimeInterface
     */
    public function getTime(): TimeInterface;


    /**
     * Set the start time of the alarm.
     *
     * @param TimeInterface $time The time to set the alarm for
     *
     * @return AlarmInterface
     */
    public function setTime(TimeInterface $time): AlarmInterface;


    /**
     * Get the duration of the alarm.
     *
     * @return TimeInterface
     */
    public function getDuration(): TimeInterface;


    /**
     * Set the duration of the alarm.
     *
     * @param TimeInterface $duration The duration of the alarm
     *
     * @return AlarmInterface
     */
    public function setDuration(TimeInterface $duration): AlarmInterface;


    /**
     * Get the frequency of the alarm.
     *
     * The result is an integer which can be compared using the bitwise operators and the class constants for each day.
     * If the alarm is a one time only alarm then it will only be equal to the class constant ONCE (none of the days).
     *
     * @return int
     */
    public function getFrequency(): int;


    /**
     * Set the frequency of the alarm.
     *
     * @param int $frequency The integer representing the frequency (using the bitwise class constants)
     *
     * @return AlarmInterface
     */
    public function setFrequency(int $frequency): AlarmInterface;


    /**
     * Check or set whether this alarm is active on mondays.
     *
     * @param bool $set Set this alarm to be active or not on mondays
     *
     * @return bool|static Returns true/false when checking, or static when setting
     */
    public function onMonday(bool $set = null);


    /**
     * Check or set whether this alarm is active on tuesdays.
     *
     * @param bool $set Set this alarm to be active or not on tuesdays
     *
     * @return bool|static Returns true/false when checking, or static when setting
     */
    public function onTuesday(bool $set = null);


    /**
     * Check or set whether this alarm is active on wednesdays.
     *
     * @param bool $set Set this alarm to be active or not on wednesdays
     *
     * @return bool|static Returns true/false when checking, or static when setting
     */
    public function onWednesday(bool $set = null);


    /**
     * Check or set whether this alarm is active on thursdays.
     *
     * @param bool $set Set this alarm to be active or not on thursdays
     *
     * @return bool|static Returns true/false when checking, or static when setting
     */
    public function onThursday(bool $set = null);


    /**
     * Check or set whether this alarm is active on fridays.
     *
     * @param bool $set Set this alarm to be active or not on fridays
     *
     * @return bool|static Returns true/false when checking, or static when setting
     */
    public function onFriday(bool $set = null);


    /**
     * Check or set whether this alarm is active on saturdays.
     *
     * @param bool $set Set this alarm to be active or not on saturdays
     *
     * @return bool|static Returns true/false when checking, or static when setting
     */
    public function onSaturday(bool $set = null);


    /**
     * Check or set whether this alarm is active on sundays.
     *
     * @param bool $set Set this alarm to be active or not on sundays
     *
     * @return bool|static Returns true/false when checking, or static when setting
     */
    public function onSunday(bool $set = null);


    /**
     * Check or set whether this alarm is a one time only alarm.
     *
     * @param bool $set Set this alarm to be a one time only alarm
     *
     * @return bool|static Returns true/false when checking, or static when setting
     */
    public function once(bool $set = null);


    /**
     * Check or set whether this alarm runs every day or not.
     *
     * @param bool $set Set this alarm to be active every day
     *
     * @return bool|static Returns true/false when checking, or static when setting
     */
    public function daily(bool $set = null);


    /**
     * Get the frequency of the alarm as a human readable description.
     *
     * @return string
     */
    public function getFrequencyDescription(): string;


    /**
     * Get the volume of the alarm.
     *
     * @return int
     */
    public function getVolume(): int;


    /**
     * Set the volume of the alarm.
     *
     * @param int $volume The volume of the alarm
     *
     * @return AlarmInterface
     */
    public function setVolume(int $volume): AlarmInterface;


    /**
     * Check if repeat is active.
     *
     * @return bool
     */
    public function getRepeat(): bool;


    /**
     * Turn repeat mode on or off.
     *
     * @param bool $repeat Whether repeat should be on or not
     *
     * @return AlarmInterface
     */
    public function setRepeat(bool $repeat): AlarmInterface;


    /**
     * Check if shuffle is active.
     *
     * @return bool
     */
    public function getShuffle(): bool;


    /**
     * Turn shuffle mode on or off.
     *
     * @param bool $shuffle Whether shuffle should be on or not
     *
     * @return AlarmInterface
     */
    public function setShuffle(bool $shuffle): AlarmInterface;


    /**
     * Check if the alarm is active.
     *
     * @return bool
     */
    public function isActive(): bool;


    /**
     * Make the alarm active.
     *
     * @return AlarmInterface
     */
    public function activate(): AlarmInterface;


    /**
     * Make the alarm inactive.
     *
     * @return AlarmInterface
     */
    public function deactivate(): AlarmInterface;


    /**
     * Delete this alarm.
     *
     * @return void
     */
    public function delete();
}
