<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Tracks\QueueTrack;
use duncan3dc\Sonos\Tracks\Track;
use duncan3dc\Sonos\Tracks\UriInterface;

/**
 * Provides an interface for managing the queue of a controller.
 */
class Queue implements \Countable
{
    /**
     * @var string $id The unique id of the queue.
     */
    protected $id;

    /**
     * @var int The current update id to be issued with upnp requests.
     */
    protected $updateId = 0;

    /**
     * @var Controller $controller The Controller instance this queue is for.
     */
    protected $controller;


    /**
     * Create an instance of the Queue class.
     *
     * @param Controller $controller The Controller instance that this queue is for
     */
    public function __construct(Controller $controller)
    {
        $this->id = "Q:0";
        $this->updateId = false;
        $this->controller = $controller;
    }


    /**
     * Send a soap request to the controller for this queue.
     *
     * @param string $service The service to send the request to
     * @param string $action The action to call
     * @param array $params The parameters to pass
     *
     * @return mixed
     */
    protected function soap($service, $action, $params = [])
    {
        $params["ObjectID"] = $this->id;

        if ($action === "Browse") {
            $params["Filter"] = "";
            $params["SortCriteria"] = "";
        }

        return $this->controller->soap($service, $action, $params);
    }


    /**
     * Send a browse request to the controller to get queue info.
     *
     * @param string $type The type of browse request to send
     * @param int $start The position to start browsing from
     * @param int $limit The number of tracks from the queue to return
     *
     * @return mixed
     */
    protected function browse($type, $start = 0, $limit = 1)
    {
        return $this->soap("ContentDirectory", "Browse", [
            "BrowseFlag"        =>  "Browse" . $type,
            "StartingIndex"     =>  $start,
            "RequestedCount"    =>  $limit,
            "Filter"            =>  "",
            "SortCriteria"      =>  "",
        ]);
    }


    /**
     * Get the next update id, or used the previously cached one.
     *
     * @return int
     */
    protected function getUpdateId()
    {
        if (!$this->updateId) {
            $data = $this->browse("DirectChildren");
            $this->updateId = $data["UpdateID"];
        }
        return $this->updateId;
    }


    /**
     * The the number of tracks in the queue.
     *
     * @return int
     */
    public function count()
    {
        $data = $this->browse("DirectChildren");
        return $data["TotalMatches"];
    }


    /**
     * Get tracks from the queue.
     *
     * @param int $start The zero-based position in the queue to start from
     * @param int $total The maximum number of tracks to return
     *
     * @return QueueTrack[]
     */
    public function getTracks($start = 0, $total = 0)
    {
        $tracks = [];

        if ($total > 0 && $total < 100) {
            $limit = $total;
        } else {
            $limit = 100;
        }

        do {
            $data = $this->browse("DirectChildren", $start, $limit);
            $parser = new XmlParser($data["Result"]);
            foreach ($parser->getTags("item") as $item) {
                $tracks[] = QueueTrack::createFromXml($item, $this->controller);
                if ($total > 0 && count($tracks) >= $total) {
                    return $tracks;
                }
            }

            $start += $limit;
        } while ($data["TotalMatches"] && count($tracks) < $data["TotalMatches"]);

        return $tracks;
    }


    /**
     * Add a track to the queue.
     *
     * @param string|UriInterface $track The URI of the track to add, or an object that implements the UriInterface
     * @param int $position The position to insert the track in the queue (zero-based), by default the track will be added to the end of the queue
     *
     * @return boolean
     */
    public function addTrack($track, $position = null)
    {
        return $this->addTracks([$track], $position);
    }


    /**
     * Add tracks to the queue.
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
            $position = $data["TotalMatches"] + 1;
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

            $data = $this->soap("AVTransport", "AddURIToQueue", [
                "UpdateID"                          =>  $this->updateId,
                "EnqueuedURI"                       =>  $track->getUri(),
                "EnqueuedURIMetaData"               =>  $track->getMetaData(),
                "DesiredFirstTrackNumberEnqueued"   =>  $position++,
                "EnqueueAsNext"                     =>  0,
            ]);
            $this->updateId++;

            if ($data["NumTracksAdded"] != 1) {
                return false;
            }
        }
        return true;
    }


    /**
     * Remove a track from the queue.
     *
     * @param int $position The zero-based position of the track to remove
     *
     * @return boolean
     */
    public function removeTrack($position)
    {
        return $this->removeTracks([$position]);
    }


    /**
     * Remove tracks from the queue.
     *
     * @param int[] $positions The zero-based positions of the tracks to remove
     *
     * @return boolean
     */
    public function removeTracks(array $positions)
    {
        $ranges = [];
        $key = 0;
        $last = -1;
        sort($positions);
        foreach ($positions as $position) {
            $position++;
            if ($last > -1) {
                if ($position === $last + 1) {
                    $ranges[$key]++;
                    $last = $position;
                    continue;
                }
            }
            $key = $position;
            $ranges[$key] = 1;
            $last = $position;
        }

        $offset = 0;
        foreach ($ranges as $position => $limit) {
            $position -= $offset;
            $data = $this->soap("AVTransport", "RemoveTrackRangeFromQueue", [
                "UpdateID"          =>  $this->getUpdateID(),
                "StartingIndex"     =>  $position,
                "NumberOfTracks"    =>  $limit,
            ]);
            $this->updateId = $data;
            $offset += $limit;
        }
        return true;
    }


    /**
     * Remove all tracks from the queue.
     *
     * @return void
     */
    public function clear()
    {
        $this->soap("AVTransport", "RemoveAllTracksFromQueue");
    }
}
