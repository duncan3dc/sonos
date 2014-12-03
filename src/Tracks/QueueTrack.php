<?php

namespace duncan3dc\Sonos\Tracks;

use duncan3dc\DomParser\XmlBase;
use duncan3dc\Sonos\Controller;

/**
 * Representation of a track from a queue.
 */
class QueueTrack extends Track
{
    /**
     * @var string $queueId The id of the track in the queue.
     */
    public $queueId = "";

    /**
     * Update the track properties using an xml element.
     *
     * @param XmlBase $xml The xml element representing the track meta data.
     * @param Controller $controller A controller instance on the playlist's network
     *
     * @return static
     */
    public static function createFromXml(XmlBase $xml, Controller $controller)
    {
        $track = parent::createFromXml($xml, $controller);

        $track->queueId = $xml->getAttribute("id");

        return $track;
    }
}
