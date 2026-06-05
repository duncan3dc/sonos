<?php

namespace duncan3dc\Sonos\Tracks;

use duncan3dc\Sonos\Exceptions\InvalidArgumentException;
use duncan3dc\Sonos\Helper;

use function strlen;
use function strpos;
use function substr;
use function urlencode;

final class Tidal extends Track
{
    private const TRACK_HASH = "10036028track%2f";
    private const UNIQUE = "track";
    public const PREFIX = "x-sonos-http:" . self::UNIQUE;
    private const SERVICE = "174";


    public static function fromId(string $id, int $account = 1): self
    {
        $uri = self::PREFIX . urlencode("/{$id}.flac") . "?sid=" . self::SERVICE . "&flags=24616&sn={$account}";
        return new self($uri);
    }


    /**
     * @param string $uri The URI of the track or the Tidal ID of the track
     */
    public function __construct(string $uri)
    {
        if (substr($uri, 0, strlen(self::PREFIX)) !== self::PREFIX) {
            throw new InvalidArgumentException("This does not look like a Tidal URI: {$uri}");
        }

        parent::__construct($uri);
    }


    public function getMetaData(): string
    {
        $id = substr($this->getUri(), strlen(self::PREFIX) + 3);
        if ($pos = strpos($id, ".flac")) {
            $id = substr($id, 0, $pos);
        }

        return Helper::createMetaDataXml(self::TRACK_HASH . $id, "-1", [
            "upnp:class" => "object.item.audioItem.musicTrack",
        ], "44551");
    }
}
