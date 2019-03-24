<?php

namespace duncan3dc\Sonos;

use duncan3dc\Sonos\Interfaces\UriInterface;

/**
 * Representation of a URI.
 */
class Uri implements UriInterface
{
    /** @var string */
    private $uri = "";

    /** @var string */
    private $metadata = "";


    /**
     * @param string $uri
     * @param string $metadata
     */
    public function __construct(string $uri, string $metadata)
    {
        $this->uri = $uri;
        $this->metadata = $metadata;
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
        return $this->metadata;
    }
}
