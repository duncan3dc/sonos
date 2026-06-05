<?php

namespace duncan3dc\Sonos\Interfaces;

/**
 * An interface for objects that repsent some type of Uri.
 */
interface UriInterface
{
    /**
     * Get the URI for this object.
     */
    public function getUri(): string;

    /**
     * Get the metadata xml for this object.
     */
    public function getMetaData(): string;
}
