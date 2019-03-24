<?php

namespace duncan3dc\Sonos\Interfaces;

use duncan3dc\Sonos\Interfaces\TrackInterface;
use duncan3dc\Sonos\Interfaces\UriInterface;

/**
 * Provides an interface for managing the queue of a controller.
 */
interface QueueInterface extends \Countable
{
    /**
     * Get tracks from the queue.
     *
     * @param int $start The zero-based position in the queue to start from
     * @param int $total The maximum number of tracks to return
     *
     * @return TrackInterface[]
     */
    public function getTracks(int $start = 0, int $total = 0): array;


    /**
     * Add a track to the queue.
     *
     * @param string|UriInterface $track The URI of the track to add, or an object that implements the UriInterface
     * @param int $position The position to insert the track in the queue (zero-based),
     *                      by default the track will be added to the end of the queue
     *
     * @return QueueInterface
     */
    public function addTrack($track, int $position = null): QueueInterface;


    /**
     * Add tracks to the queue.
     *
     * @param string[]|UriInterface[] $tracks An array where each element is either the URI of the tracks to add,
     *                                          or an object that implements the UriInterface
     * @param int $position The position to insert the tracks in the queue (zero-based),
     *                      by default the tracks will be added to the end of the queue
     *
     * @return QueueInterface
     */
    public function addTracks(array $tracks, int $position = null): QueueInterface;


    /**
     * Remove a track from the queue.
     *
     * @param int $position The zero-based position of the track to remove
     *
     * @return bool
     */
    public function removeTrack(int $position): bool;


    /**
     * Remove tracks from the queue.
     *
     * @param int[] $positions The zero-based positions of the tracks to remove
     *
     * @return bool
     */
    public function removeTracks(array $positions): bool;


    /**
     * Remove all tracks from the queue.
     *
     * @return QueueInterface
     */
    public function clear(): QueueInterface;
}
