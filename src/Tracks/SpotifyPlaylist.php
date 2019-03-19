<?php

namespace duncan3dc\Sonos\Tracks;

use duncan3dc\Sonos\Helper;

/**
 * Representation of a Spotify playlist.
 */
class SpotifyPlaylist extends Track
{
    const PREFIX = "x-rincon-cpcontainer:" . Helper::PLAYLIST_HASH;
    const REGION_EU = "2311";
    const REGION_US = "3079";

    /**
     * @var string $region The region code for the Spotify service (the default is EU).
     */
    public static $region = self::REGION_EU;


    /**
     * Create a Spotify playlist object.
     *
     * @param string $uri The URI of the playlist or the full Spotify ID of the playlist
     */
    public function __construct(string $uri)
    {
        # If this is a spotify playlist ID and not a URI then convert it to a URI now
        if (substr($uri, 0, strlen(self::PREFIX)) !== self::PREFIX) {
            $uri = self::PREFIX . urlencode($uri);
        }

        parent::__construct($uri);
    }

    /**
     * Get the metadata xml for this playlist.
     *
     * @return string
     */
    public function getMetaData(): string
    {
        $uri = substr($this->getUri(), strlen(self::PREFIX));

        return Helper::createMetaDataXml(Helper::PLAYLIST_HASH . "{$uri}", "-1", [
            "dc:title"      =>  "",
            "upnp:class"    =>  "object.container.playlistContainer",
        ], static::$region);
    }
}
