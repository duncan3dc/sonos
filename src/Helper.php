<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;

/**
 * Provides helper functions for the classes.
 */
class Helper extends \duncan3dc\Helpers\Helper
{

    /**
     * Extract track data from the passed content.
     *
     * @param mixed $xml
     *
     * @return array Track data containing the following elements (title, atrist, album, track-number, album-art)
     */
    public static function getTrackMetaData($xml)
    {
        if (is_object($xml)) {
            $parser = $xml;
        } elseif ($xml) {
            $parser = new XmlParser($xml);
        } else {
            return [];
        }

        if ($album = (string) $parser->getTag("albumArtURI")) {
            $album = sprintf("http://%s:1400%s", Network::getController()->ip, $album);
        }

        return [
            "title"         =>  (string) $parser->getTag("title"),
            "artist"        =>  (string) $parser->getTag("creator"),
            "album"         =>  (string) $parser->getTag("album"),
            "track-number"  =>  (int)(string) $parser->getTag("originalTrackNumber"),
            "album-art"     =>  $album,
        ];
    }
}
