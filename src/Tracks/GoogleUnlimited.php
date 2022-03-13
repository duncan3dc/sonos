<?php

namespace duncan3dc\Sonos\Tracks;

/**
 * Representation of a Google unlimited track.
 */
class GoogleUnlimited extends Google
{
    protected const UNIQUE = "A0DvPDnows";
    public const PREFIX = "x-sonos-http:" . self::UNIQUE;
}
