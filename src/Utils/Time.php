<?php

namespace duncan3dc\Sonos\Utils;

use duncan3dc\Sonos\Common\Utils\Time as CommonTime;
use duncan3dc\Sonos\Interfaces\Utils\TimeInterface;

/**
 * A class to represent amounts of time.
 */
final class Time implements TimeInterface
{
    /**
     * @var int $seconds The number of seconds this instance represents.
     */
    private $seconds = 0;


    /**
     * Create a new instance from a number of seconds.
     *
     * @param int $seconds The number of seconds
     *
     * @return TimeInterface
     */
    public static function inSeconds(int $seconds): TimeInterface
    {
        return new self($seconds);
    }


    /**
     * Create a new instance from a time in the format hh:mm:ss.
     *
     * @param string $string The time to parse
     *
     * @return TimeInterface
     */
    public static function parse(string $string): TimeInterface
    {
        $time = CommonTime::parse($string);
        return new self($time->asInt());
    }


    /**
     * Create a new time instance representing the start.
     *
     * @return TimeInterface
     */
    public static function start(): TimeInterface
    {
        return new self(0);
    }


    /**
     * Create a new instance from a number of seconds.
     *
     * @param int $seconds The number of seconds
     */
    private function __construct($seconds)
    {
        $this->seconds = $seconds;
    }


    /**
     * Get the number of seconds this instance represents.
     *
     * @return int
     */
    public function asInt(): int
    {
        return $this->seconds;
    }


    /**
     * Get the time in the format hh:mm:ss.
     *
     * @return string
     */
    public function asString(): string
    {
        return CommonTime::inSeconds($this->seconds)->asString();
    }


    /**
     * Get the time in the format hh:mm:ss.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) CommonTime::inSeconds($this->seconds);
    }


    /**
     * Get the seconds portion of the time.
     *
     * @return int
     */
    public function getSeconds(): int
    {
        return CommonTime::inSeconds($this->seconds)->getSeconds();
    }


    /**
     * Get the minutes portion of the time.
     *
     * @return int
     */
    public function getMinutes(): int
    {
        return CommonTime::inSeconds($this->seconds)->getMinutes();
    }


    /**
     * Get the hours portion of the time.
     *
     * @return int
     */
    public function getHours(): int
    {
        return CommonTime::inSeconds($this->seconds)->getHours();
    }


    /**
     * Format the time in a custom way.
     *
     * @param string $format The custom format to use. %h, %m, %s are available,
     *                     and uppercase versions (%H, %M, %S) ensure a leading zero is present for single digit values
     *
     * @return string
     */
    public function format(string $format): string
    {
        return CommonTime::inSeconds($this->seconds)->format($format);
    }
}
