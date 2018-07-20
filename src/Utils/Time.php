<?php

namespace duncan3dc\Sonos\Utils;

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
        $bits = explode(":", $string);

        $seconds = (int) array_pop($bits);

        if (count($bits) > 0) {
            $minutes = (int) array_pop($bits);
            $seconds += ($minutes * 60);

            if (count($bits) > 0) {
                $hours = (int) array_pop($bits);
                $seconds += ($hours * 60 * 60);
            }
        }

        return new self($seconds);
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
        return $this->format("%H:%M:%S");
    }


    /**
     * Get the time in the format hh:mm:ss.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->asString();
    }


    /**
     * Get the seconds portion of the time.
     *
     * @return int
     */
    public function getSeconds(): int
    {
        return $this->seconds % 60;
    }


    /**
     * Get the minutes portion of the time.
     *
     * @return int
     */
    public function getMinutes(): int
    {
        $minutes = (int) floor($this->seconds / 60);
        return $minutes % 60;
    }


    /**
     * Get the hours portion of the time.
     *
     * @return int
     */
    public function getHours(): int
    {
        return (int) floor($this->seconds / 3600);
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
        $hours = $this->getHours();
        $minutes = $this->getMinutes();
        $seconds = $this->getSeconds();

        $replace = [
            "%h"    =>  $hours,
            "%H"    =>  sprintf("%02s", $hours),
            "%m"    =>  $minutes,
            "%M"    =>  sprintf("%02s", $minutes),
            "%s"    =>  $seconds,
            "%S"    =>  sprintf("%02s", $seconds),
        ];

        return str_replace(array_keys($replace), array_values($replace), $format);
    }
}
