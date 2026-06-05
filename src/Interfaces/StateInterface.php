<?php

namespace duncan3dc\Sonos\Interfaces;

use duncan3dc\Sonos\Interfaces\Utils\TimeInterface;
use duncan3dc\Sonos\Tracks\Stream;

/**
 * Representation of the current state of a controller.
 */
interface StateInterface extends TrackInterface
{
    /**
     * Check if this state is currently playing a stream.
     */
    public function isStreaming(): bool;

    /**
     * Set the stream object in use.
     */
    public function setStream(Stream $stream): StateInterface;

    /**
     * Get the stream object in use (or null if we are not on a stream).
     */
    public function getStream(): ?TrackInterface;

    /**
     * Set the duration of the currently active track.
     */
    public function setDuration(TimeInterface $duration): StateInterface;

    /**
     * Get the duration of the currently active track.
     */
    public function getDuration(): TimeInterface;

    /**
     * Set the position of the currently active track.
     */
    public function setPosition(TimeInterface $position): StateInterface;

    /**
     * Get the position of the currently active track.
     */
    public function getPosition(): TimeInterface;
}
