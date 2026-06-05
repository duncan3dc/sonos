<?php

namespace duncan3dc\Sonos\Interfaces;

use duncan3dc\Sonos\Interfaces\Utils\TimeInterface;
use duncan3dc\Sonos\Tracks\Stream;

/**
 * Representation of the current state of a controller.
*/
interface ControllerStateInterface
{
    /**
     * Get the playing mode of the controller.
     */
    public function getState(): PlayState;

    /**
     * Get the number of the active track in the queue
     *
     * @return int The zero-based number of the track in the queue
     */
    public function getTrack(): int;

    /**
     * Get the position of the currently active track.
     */
    public function getPosition(): TimeInterface;

    /**
     * Check if repeat is currently active.
     */
    public function getRepeat(): bool;

    /**
     * Check if shuffle is currently active.
     */
    public function getShuffle(): bool;

    /**
     * Check if crossfade is currently active.
     */
    public function getCrossfade(): bool;

    /**
     * Each speaker's UUID and its volume.
     *
     * @return array<string, int>
     */
    public function getSpeakers(): array;

    /**
     * Get the tracks that are in the queue.
     *
     * @return TrackInterface[]
     */
    public function getTracks(): array;

    /**
     * Get the stream this controller is using.
     */
    public function getStream(): ?Stream;
}
