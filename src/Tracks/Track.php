<?php

namespace duncan3dc\Sonos\Tracks;

use duncan3dc\DomParser\XmlElement;
use duncan3dc\Sonos\Helper;
use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\Interfaces\TrackInterface;

/**
 * Representation of a track.
 */
class Track implements TrackInterface
{
    /**
     * @var string $uri The uri of the track.
     */
    private $uri = "";

    /**
     * @var string $title The name of the track.
     */
    private $title = "";

    /**
     * @var string $artist The name of the artist of the track.
     */
    private $artist = "";

    /**
     * @var string $album The name of the album of the track.
     */
    private $album = "";

    /**
     * @var int $number The number of the track.
     */
    private $number = 0;

    /**
     * @var string $albumArt The full path to the album art for this track.
     */
    private $albumArt = "";

    /**
     * @var string $itemId The id of the item.
     */
    private $itemId = "-1";


    /**
     * Create a Track object.
     *
     * @param string $uri The URI of the track
     */
    public function __construct(string $uri)
    {
        $this->uri = $uri;
    }


    /**
     * Get the URI for this track.
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }


    /**
     * Get the metadata xml for this track.
     *
     * @return string
     */
    public function getMetaData(): string
    {
        return Helper::createMetaDataXml($this->itemId, "-1", [
            "res"               =>  $this->uri,
            "upnp:albumArtURI"  =>  $this->albumArt,
            "dc:title"          =>  $this->title,
            "upnp:class"        =>  "object.item.audioItem.musicTrack",
            "dc:creator"        =>  $this->artist,
            "upnp:album"        =>  $this->album,
        ]);
    }


    /**
     * Set the name of the track.
     *
     * @param string $title The title of the track
     *
     * @return $this
     */
    public function setTitle(string $title): TrackInterface
    {
        $this->title = $title;
        return $this;
    }


    /**
     * Get the name of the track.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }


    /**
     * Set the artist of the track.
     *
     * @param string $artist The artist of the track
     *
     * @return $this
     */
    public function setArtist(string $artist): TrackInterface
    {
        $this->artist = $artist;
        return $this;
    }


    /**
     * Get the name of the artist of the track.
     *
     * @return string
     */
    public function getArtist(): string
    {
        return $this->artist;
    }


    /**
     * Set the album of the track.
     *
     * @param string $album The album of the track
     *
     * @return $this
     */
    public function setAlbum(string $album): TrackInterface
    {
        $this->album = $album;
        return $this;
    }


    /**
     * Get the name of the album of the track.
     *
     * @return string
     */
    public function getAlbum(): string
    {
        return $this->album;
    }


    /**
     * Set the number of the track.
     *
     * @param int $number The number of the track
     *
     * @return $this
     */
    public function setNumber(int $number): TrackInterface
    {
        $this->number = $number;
        return $this;
    }


    /**
     * Get the track number.
     *
     * @return int
     */
    public function getNumber(): int
    {
        return $this->number;
    }


    /**
     * Set the album art of the track.
     *
     * @param string $albumArt The albumArt of the track
     *
     * @return $this
     */
    public function setAlbumArt(string $albumArt): TrackInterface
    {
        $this->albumArt = $albumArt;
        return $this;
    }


    /**
     * @var string $albumArt The full path to the album art for this track.
     *
     * @return string
     */
    public function getAlbumArt(): string
    {
        return $this->albumArt;
    }


    /**
     * Update the track properties using an xml element.
     *
     * @param XmlElement $xml The xml element representing the track meta data
     * @param ControllerInterface $controller A controller instance to communicate with
     *
     * @return self
     */
    public static function createFromXml(XmlElement $xml, ControllerInterface $controller): TrackInterface
    {
        $track = new static($xml->getTag("res"));

        $track->title = (string) $xml->getTag("title");

        if ($stream = (string) $xml->getTag("streamContent")) {
            $bits = explode(" - ", $stream);
            $track->artist = array_shift($bits);
            $track->title = implode(" - ", $bits);
            $track->album = "";
        } else {
            $track->artist = (string) $xml->getTag("creator");
            $track->album = (string) $xml->getTag("album");
        }

        # Cast the node to a string first (we do this instead of calling nodeValue in case the object is null)
        $number = (string) $xml->getTag("originalTrackNumber");

        # Then convert to a number
        $track->number = (int) $number;

        if ($art = (string) $xml->getTag("albumArtURI")) {
            if (substr($art, 0, 4) !== "http") {
                $art = ltrim($art, "/");
                $art = sprintf("http://%s:1400/%s", $controller->getIp(), $art);
            }
            $track->albumArt = $art;
        }

        if ($xml->hasAttribute("id")) {
            $track->itemId = $xml->getAttribute("id");
        }

        return $track;
    }
}
