<?php

namespace duncan3dc\Sonos\Tracks;

use duncan3dc\Sonos\Helper;

/**
 * Representation of a Spotify track.
 */
class Spotify extends Track
{
    public const PREFIX = "x-sonos-spotify:";
    public const REGION_EU = "2311";
    public const REGION_US = "3079";

    /**
     * @var string $region The region code for the Spotify service (the default is EU).
     */
    public static $region = self::REGION_EU;


    /**
     * Create a Spotify track object.
     *
     * @param string $uri The URI of the track or the Spotify ID of the track
     */
    public function __construct(string $uri)
    {
        # If this is a spotify track ID and not a URI then convert it to a URI now
        if (substr($uri, 0, strlen(self::PREFIX)) !== self::PREFIX) {
            $uri = self::PREFIX . urlencode("spotify:track:{$uri}");
        }

        parent::__construct($uri);
    }


    /**
     * Get the metadata xml for this track.
     *
     * @return string
     */
    public function getMetaData(): string
    {
        $uri = substr($this->getUri(), strlen(self::PREFIX));

        return Helper::createMetaDataXml(Helper::TRACK_HASH . "{$uri}", "-1", [
            "dc:title"      =>  "",
            "upnp:class"    =>  "object.item.audioItem.musicTrack",
        ], static::$region);
    }
}
