<?php

namespace duncan3dc\Sonos\Tracks;

use duncan3dc\Sonos\Helper;

/**
 * Representation of a Deezer track.
 */
class Deezer extends Track
{
    const PREFIX = "x-sonos-http:";

    /**
     * Create a Deezer track object.
     *
     * @param string $uri The URI of the track or the Deezer ID of the track
     */
    public function __construct($uri)
    {
        # If this is a Deezer track ID and not a URI then convert it to a URI now
        if (substr($uri, 0, strlen(self::PREFIX)) !== self::PREFIX) {
            $uri = self::PREFIX . urlencode("tr:{$uri}.mp3");
        }

        parent::__construct($uri);
    }


    /**
     * Get the metadata xml for this track.
     *
     * @return string
     */
    public function getMetaData()
    {
        $uri = substr($this->uri, strlen(self::PREFIX), -4);

        return Helper::createMetaDataXml(Helper::TRACK_HASH . "{$uri}", "-1", [
            "dc:title"      =>  "",
            "upnp:class"    =>  "object.item.audioItem.musicTrack",
            "desc"          =>  [
                "_attributes"   =>  [
                    "id"        =>  "cdudn",
                    "nameSpace" =>  "urn:schemas-rinconnetworks-com:metadata-1-0/",
                ],
                "_value"        =>  "SA_RINCON519_X_#Svc519-0-Token",
            ],
        ]);
    }
}
