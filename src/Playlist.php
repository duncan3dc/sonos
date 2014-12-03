<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlElement;
use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Tracks\Track;
use duncan3dc\Sonos\Tracks\UriInterface;

/**
 * Provides an interface for managing Sonos playlists on the current network.
 */
class Playlist extends Queue
{
    /**
     * @var string $name The name of the playlist.
     */
    protected $name = false;


    /**
     * Create an instance of the Playlist class.
     *
     * @param string|XmlElement $param The id of the playlist, or an xml element with the relevant attributes
     * @param Controller $controller A controller instance on the playlist's network
     */
    public function __construct($param, Controller $controller)
    {
        if (is_string($param)) {
            $this->id = $param;
            $this->name = false;
        } else {
            $this->id = $param->getAttribute("id");
            $this->name = $param->getTag("title")->nodeValue;
        }

        $this->updateId = false;
        $this->controller = $controller;
    }


    /**
     * Get the id of the playlist.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Get the name of the playlist.
     *
     * @return string
     */
    public function getName()
    {
        if (!$this->name) {
            $data = $this->browse("Metadata");
            $xml = new XmlParser($data["Result"]);
            $this->name = $xml->getTag("title")->nodeValue;
        }
        return $this->name;
    }


    /**
     * Add tracks to the playlist.
     *
     * @param string[]|UriInterface[] $tracks An array where each element is either the URI of the tracks to add, or an object that implements the UriInterface
     * @param int $position The position to insert the tracks in the queue (zero-based), by default the tracks will be added to the end of the queue
     *
     * @return boolean
     */
    public function addTracks(array $tracks, $position = null)
    {
        if ($position === null) {
            $data = $this->browse("DirectChildren");
            $this->updateId = $data["UpdateID"];
            $position = $data["TotalMatches"];
        }

        # Ensure the update id is set to begin with
        $this->getUpdateID();

        foreach ($tracks as $track) {

            # If a simple uri has been passed then convert it to a Track instance
            if (is_string($track)) {
                $track = new Track($track);
            }

            if (!$track instanceof UriInterface) {
                throw new \InvalidArgumentException("The addTracks() array must contain either string URIs or objects that implement \duncan3dc\Sonos\Tracks\UriInterface");
            }

            $data = $this->soap("AVTransport", "AddURIToSavedQueue", [
                "UpdateID"              =>  $this->updateId,
                "EnqueuedURI"           =>  $track->getUri(),
                "EnqueuedURIMetaData"   =>  "",
                "AddAtIndex"            =>  $position++,
            ]);
            $this->updateId = $data["NewUpdateID"];

            if ($data["NumTracksAdded"] != 1) {
                return false;
            }
        }
        return true;
    }


    /**
     * Remove tracks from the playlist.
     *
     * @param int[] $positions The zero-based positions of the tracks to remove
     *
     * @return boolean
     */
    public function removeTracks(array $positions)
    {
        $data = $this->soap("AVTransport", "ReorderTracksInSavedQueue", [
            "UpdateID"              =>  $this->getUpdateID(),
            "TrackList"             =>  implode(",", $positions),
            "NewPositionList"       =>  "",
        ]);
        $this->updateId = $data["NewUpdateID"];

        return ($data["QueueLengthChange"] == (count($positions) * -1));
    }


    /**
     * Remove all tracks from the queue.
     *
     * @return void
     */
    public function clear()
    {
        $positions = [];
        $max = $this->count();
        for ($i = 0; $i < $max; $i++) {
            $positions[] = $i;
        }
        $this->removeTracks($positions);
    }


    /**
     * Delete this playlist from the network.
     *
     * @return void
     */
    public function delete()
    {
        $this->soap("ContentDirectory", "DestroyObject");
    }
}
