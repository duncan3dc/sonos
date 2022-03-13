<?php

namespace duncan3dc\Sonos\Tracks;

use duncan3dc\DomParser\XmlElement;
use duncan3dc\Sonos\Helper;
use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\Interfaces\TrackInterface;

/**
 * Representation of a stream.
 */
class Stream extends Track
{
    public const PREFIX = "x-sonosapi-stream";

    /**
     * Create a Stream object.
     *
     * @param string $uri The URI of the stream
     * @param string $title The title of the stream
     */
    public function __construct(string $uri, string $title = "")
    {
        parent::__construct($uri);

        $this->setTitle($title);
    }


    /**
     * Get the metadata xml for this stream.
     *
     * @return string
     */
    public function getMetaData(): string
    {
        return Helper::createMetaDataXml("-1", "-1", [
            "dc:title"          =>  $this->getTitle() ?: "Stream",
            "upnp:class"        =>  "object.item.audioItem.audioBroadcast",
            "desc"              =>  [
                "_attributes"       =>  [
                    "id"        =>  "cdudn",
                    "nameSpace" =>  "urn:schemas-rinconnetworks-com:metadata-1-0/",
                ],
                "_value"            =>  "SA_RINCON65031_",
            ],
        ]);
    }


    /**
     * Create a stream from an xml element.
     *
     * @param XmlElement $xml The xml element representing the track meta data
     * @param ControllerInterface $controller A controller instance to communicate with
     *
     * @return self
     */
    public static function createFromXml(XmlElement $xml, ControllerInterface $controller): TrackInterface
    {
        return new static($xml->getTag("res")->nodeValue, $xml->getTag("title")->nodeValue);
    }
}
