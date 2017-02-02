<?php

namespace duncan3dc\Sonos\Interfaces;

/**
 * Provides an interface for managing Sonos playlists on the current network.
 */
interface PlaylistInterface extends QueueInterface
{

    /**
     * Get the id of the playlist.
     *
     * @return string
     */
    public function getId(): string;


    /**
     * Get the name of the playlist.
     *
     * @return string
     */
    public function getName(): string;


    /**
     * Move a track from one position in the playlist to another.
     *
     * @param int $from The current position of the track in the playlist (zero-based)
     * @param int $to The desired position in the playlist (zero-based)
     *
     * @return PlaylistInterface
     */
    public function moveTrack(int $from, int $to): PlaylistInterface;


    /**
     * Delete this playlist from the network.
     *
     * @return void
     */
    public function delete();
}
