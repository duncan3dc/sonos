<?php

namespace duncan3dc\Sonos\Tracks;

use duncan3dc\DomParser\XmlWriter;

/**
 * Representation of a stream.
 */
class Stream implements UriInterface
{
    /**
     * @var string $uri The uri of the stream.
     */
    protected $uri = "";


    /**
     * Create a Stream object.
     *
     * @param string $uri The URI of the stream
     */
    public function __construct($uri)
    {
        $this->uri = (string) $uri;
    }


    /**
     * Get the URI for this stream.
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }


    /**
     * Get the metadata xml for this stream.
     *
     * @return string
     */
    public function getMetaData()
    {
        $xml = XmlWriter::createXml([
            "DIDL-Lite" =>  [
                "_attributes"   =>  [
                    "xmlns:dc"      =>  "http://purl.org/dc/elements/1.1/",
                    "xmlns:upnp"    =>  "urn:schemas-upnp-org:metadata-1-0/upnp/",
                    "xmlns:r"       =>  "urn:schemas-rinconnetworks-com:metadata-1-0/",
                    "xmlns"         =>  "urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/",
                ],
                "item"  =>  [
                    "_attributes"   =>  [
                        "id"            =>  "-1",
                        "parentID"      =>  "-1",
                        "restricted"    =>  "true",
                    ],
                    "dc:title"          =>  "Stream",
                    "upnp:class"        =>  "object.item.audioItem.audioBroadcast",
                    "desc"              =>  [
                        "_attributes"       =>  [
                            "id"        =>  "cdudn",
                            "nameSpace" =>  "urn:schemas-rinconnetworks-com:metadata-1-0/",
                        ],
                        "_value"            =>  "SA_RINCON65031_",
                    ],
                ],
            ]
        ]);

        # Get rid of the xml header as only the DIDL-Lite element is required
        $meta = explode("\n", $xml)[1];

        return $meta;
    }
}
