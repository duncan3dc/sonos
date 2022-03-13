<?php

namespace duncan3dc\Sonos\Interfaces;

/**
 * Representation of a track.
 */
interface TrackInterface extends UriInterface
{
    public function __construct(string $uri);

    /**
     * Set the name of the track.
     */
    public function setTitle(string $title): TrackInterface;

    /**
     * Get the name of the track.
     */
    public function getTitle(): string;

    /**
     * Set the artist of the track.
     */
    public function setArtist(string $artist): TrackInterface;

    /**
     * Get the name of the artist of the track.
     */
    public function getArtist(): string;

    /**
     * Set the album of the track.
     */
    public function setAlbum(string $album): TrackInterface;

    /**
     * Get the name of the album of the track.
     */
    public function getAlbum(): string;

    /**
     * Set the number of the track.
     */
    public function setNumber(int $number): TrackInterface;

    /**
     * Get the track number.
     */
    public function getNumber(): int;

    /**
     * Set the album art of the track.
     */
    public function setAlbumArt(string $albumArt): TrackInterface;

    /**
     * @var string $albumArt The full path to the album art for this track.
     */
    public function getAlbumArt(): string;
}
