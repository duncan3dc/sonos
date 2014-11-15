<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlBase;

/**
 * Provides helper functions for the classes.
 */
class Helper extends \duncan3dc\Helpers\Helper
{

    /**
     * Extract track data from the passed content.
     *
     * @param XmlBase $xml
     *
     * @return array Track data containing the following elements (title, atrist, album, track-number, album-art)
     */
    public static function getTrackMetaData(XmlBase $xml)
    {
        if ($art = (string) $xml->getTag("albumArtURI")) {
            $art = sprintf("http://%s:1400%s", Network::getController()->ip, $art);
        }

        $title = (string) $xml->getTag("title");

        if ($stream = (string) $xml->getTag("streamContent")) {
            $bits = explode(" - ", $stream);
            $artist = array_shift($bits);
            $title = implode(" - ", $bits);
            $album = "";
        } else {
            $artist = (string) $xml->getTag("creator");
            $album = (string) $xml->getTag("album");
        }

        return [
            "title"         =>  $title,
            "artist"        =>  $artist,
            "album"         =>  $album,
            "track-number"  =>  (int)(string) $xml->getTag("originalTrackNumber"),
            "album-art"     =>  $art,
        ];
    }


    /**
     * Create a mode array from the mode text value.
     *
     * @param string $mode
     *
     * @return array Mode data containing the following boolean elements (shuffle, repeat)
     */
    public static function getMode($mode)
    {
        $options = [
            "shuffle"   =>  false,
            "repeat"    =>  false,
        ];

        if (in_array($mode, ["REPEAT_ALL", "SHUFFLE"])) {
            $options["repeat"] = true;
        }
        if (in_array($mode, ["SHUFFLE_NOREPEAT", "SHUFFLE"])) {
            $options["shuffle"] = true;
        }

        return $options;
    }
}
