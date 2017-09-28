<?php

namespace duncan3dc\Sonos\Tracks;

use duncan3dc\Sonos\Helper;
use duncan3dc\Sonos\Tracks\UriInterface;

/**
 * Representation of a Google instant mix.
 */
class GoogleInstantMix extends Stream
{
    const PREFIX = "x-sonosapi-radio:";

    /**
     * Create a new instance.
     *
     * @param string $uri The URI or the ID of the instant mix
     */
    public function __construct($uri, $name = "")
    {
        # If this is an instant mix ID and not a URI then convert it to a URI now
        if (substr($uri, 0, strlen(static::PREFIX)) !== static::PREFIX) {
            $uri = self::PREFIX . $uri . "?sid=151&amp;flags=8300&amp;sn=2";
        }

        parent::__construct($uri, $name);
    }


    /**
     * Get the metadata xml for this mix.
     *
     * @return string
     */
    public function getMetaData()
    {
        $id = substr($this->getUri(), strlen(self::PREFIX));

        return Helper::createMetaDataXml("100c206c{$id}", "-1", [
            "dc:title"      =>  $this->getName(),
            "upnp:class"    =>  "object.item.audioItem.audioBroadcast",
        ], "38663");
    }
}
