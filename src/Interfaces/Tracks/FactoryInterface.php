<?php

namespace duncan3dc\Sonos\Interfaces\Tracks;

use duncan3dc\Dom\ElementInterface;
use duncan3dc\Sonos\Interfaces\TrackInterface;

/**
 * Factory for creating Track instances.
 */
interface FactoryInterface
{
    /**
     * Create a new Track instance from a URI.
     *
     * @param string $uri The URI of the track
     *
     * @return TrackInterface
     */
    public function createFromUri(string $uri): TrackInterface;


    /**
     * Create a new Track instance from a URI.
     *
     * @param ElementInterface $xml The xml element representing the track meta data.
     */
    public function createFromXml(ElementInterface $xml): TrackInterface;
}
