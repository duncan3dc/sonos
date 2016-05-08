<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlElement;
use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Exceptions\SonosException;
use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\Interfaces\PlaylistInterface;
use duncan3dc\Sonos\Interfaces\QueueInterface;
use duncan3dc\Sonos\Interfaces\UriInterface;
use function substr;

/**
 * Provides an interface for managing Sonos playlists on the current network.
 */
final class Playlist extends Queue implements PlaylistInterface, UriInterface
{
    /**
     * @var string|null $name The name of the playlist.
     */
    private $name;


    /**
     * Create an instance of the Playlist class.
     *
     * @param string|XmlElement $param The id of the playlist, or an xml element with the relevant attributes
     * @param ControllerInterface $controller A controller instance on the playlist's network
     */
    public function __construct($param, ControllerInterface $controller)
    {
        parent::__construct($controller);

        if (is_string($param)) {
            $this->id = $param;
        } else {
            $this->id = $param->getAttribute("id");
            $this->name = $param->getTag("title")->nodeValue;
        }
    }


    /**
     * Get the id of the playlist.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * Get the name of the playlist.
     *
     * @return string
     */
    public function getName(): string
    {
        if ($this->name === null) {
            $data = $this->browse("Metadata");
            $xml = new XmlParser($data["Result"]);
            $this->name = $xml->getTag("title")->nodeValue;
        }
        return $this->name;
    }


    /**
     * Calculate the position number to be used to add a track to the end of the playlist.
     *
     * @return int
     */
    protected function getNextPosition(): int
    {
        return parent::getNextPosition() - 1;
    }


    /**
     * Add tracks to the playlist.
     *
     * If no $position is passed the track will be added to the end of the playlist
     *
     * @param UriInterface[] $tracks The tracks to add
     * @param int $position The position to insert the track in the playlist (zero-based)
     *
     * @return void
     */
    protected function addUris(array $tracks, int $position = null)
    {
        if ($position === null) {
            $position = $this->getNextPosition();
        }

        foreach ($tracks as $track) {
            $data = $this->soap("AVTransport", "AddURIToSavedQueue", [
                "UpdateID"              =>  $this->updateId,
                "EnqueuedURI"           =>  $track->getUri(),
                "EnqueuedURIMetaData"   =>  $track->getMetaData(),
                "AddAtIndex"            =>  $position,
            ]);
            $this->updateId = $data["NewUpdateID"];

            $position++;
        }
    }


    /**
     * Remove tracks from the playlist.
     *
     * @param int[] $positions The zero-based positions of the tracks to remove
     *
     * @return bool
     */
    public function removeTracks(array $positions): bool
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
     * Move a track from one position in the playlist to another.
     *
     * @param int $from The current position of the track in the playlist (zero-based)
     * @param int $to The desired position in the playlist (zero-based)
     *
     * @return $this
     */
    public function moveTrack(int $from, int $to): PlaylistInterface
    {
        $data = $this->soap("AVTransport", "ReorderTracksInSavedQueue", [
            "UpdateID"              =>  $this->getUpdateID(),
            "TrackList"             =>  (string) $from,
            "NewPositionList"       =>  (string) $to,
        ]);
        $this->updateId = $data["NewUpdateID"];

        return $this;
    }


    /**
     * Remove all tracks from the playlist.
     *
     * @return $this
     */
    public function clear(): QueueInterface
    {
        $positions = [];
        $max = $this->count();
        for ($i = 0; $i < $max; $i++) {
            $positions[] = $i;
        }
        $this->removeTracks($positions);

        return $this;
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


    /**
     * @inheritdoc
     */
    public function getUri(): string
    {
        $id = substr($this->id, 3);
        return "file:///jffs/settings/savedqueues.rsq#{$id}";
    }


    /**
     * @inheritdoc
     */
    public function getMetaData(): string
    {
        return Helper::createMetaDataXml($this->id, "SQ:", [
            "dc:title" => $this->getName(),
            "upnp:class" => "object.container.playlistContainer",
            "desc" => [
                "_attributes" => [
                    "id" => "cdudn",
                    "nameSpace" => "urn:schemas-rinconnetworks-com:metadata-1-0/",
                ],
                "_value" => "RINCON_AssociatedZPUDN",
            ],
        ]);
    }
}
