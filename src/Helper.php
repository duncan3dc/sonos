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
     * @return array Track data containing the following elements (title, atrist, album, track-number)
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
        return [
            "title"         =>  (string) $parser->getTag("title"),
            "artist"        =>  (string) $parser->getTag("creator"),
            "album"         =>  (string) $parser->getTag("album"),
            "track-number"  =>  (int) $parser->getTag("originalTrackNumber"),
        ];
    }
}
