<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Exceptions\SonosException;
use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\Interfaces\QueueInterface;
use duncan3dc\Sonos\Interfaces\TrackInterface;
use duncan3dc\Sonos\Interfaces\Tracks\FactoryInterface;
use duncan3dc\Sonos\Interfaces\UriInterface;
use duncan3dc\Sonos\Tracks\Factory;

use function is_string;

/**
 * Provides an interface for managing the queue of a controller.
 */
class Queue implements QueueInterface
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
     * @var ControllerInterface $controller The Controller instance this queue is for.
     */
    protected $controller;

    /**
     * @var FactoryInterface $trackFactory A factory to create tracks from.
     */
    protected $trackFactory;


    /**
     * Create an instance of the Queue class.
     *
     * @param ControllerInterface $controller The Controller instance that this queue is for
     * @param ?FactoryInterface $factory A factory to create tracks from
     */
    public function __construct(ControllerInterface $controller, ?FactoryInterface $factory = null)
    {
        $this->id = "Q:0";
        $this->updateId = 0;
        $this->controller = $controller;

        if ($factory === null) {
            $factory = new Factory($this->controller);
        }
        $this->trackFactory = $factory;
    }


    /**
     * Send a soap request to the controller for this queue.
     *
     * @param string $service The service to send the request to
     * @param string $action The action to call
     * @param array<string, string|int|bool> $params The parameters to pass
     *
     * @return mixed
     */
    protected function soap(string $service, string $action, array $params = [])
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
    protected function browse(string $type, int $start = 0, int $limit = 1)
    {
        return $this->soap("ContentDirectory", "Browse", [
            "BrowseFlag"        =>  "Browse{$type}",
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
    protected function getUpdateId(): int
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
    public function count(): int
    {
        $data = $this->browse("DirectChildren");
        return (int) $data["TotalMatches"];
    }


    /**
     * Get tracks from the queue.
     *
     * @param int $start The zero-based position in the queue to start from
     * @param int $total The maximum number of tracks to return
     *
     * @return TrackInterface[]
     */
    public function getTracks(int $start = 0, int $total = 0): array
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
                $tracks[] = $this->trackFactory->createFromXml($item);
                if ($total > 0 && count($tracks) >= $total) {
                    return $tracks;
                }
            }

            $start += $limit;
        } while ($data["NumberReturned"] && $data["TotalMatches"] && count($tracks) < $data["TotalMatches"]);

        return $tracks;
    }


    /**
     * Calculate the position number to be used to add a track to the end of the queue.
     *
     * @return int
     */
    protected function getNextPosition(): int
    {
        $data = $this->browse("DirectChildren");

        $this->updateId = $data["UpdateID"];

        return $data["TotalMatches"] + 1;
    }


    /**
     * Add multiple uris to the queue.
     *
     * @param UriInterface[] $tracks The track to add
     * @param ?int $position The position to insert the track in the queue (zero-based),
     *                      by default the track will be added to the end of the queue
     *
     * @return void
     */
    protected function addUris(array $tracks, ?int $position = null)
    {
        if ($position === null) {
            $position = $this->getNextPosition();
        }

        /**
         * It seems like adding over 16 tracks at once causes the request to fail with error 402.
         * I guess at this point Sonos decides it's more efficient to lookup the contents
         * by their container, and the call fails because we don't have a container for these tracks.
         */
        $chunks = array_chunk($tracks, 16);

        foreach ($chunks as $chunk) {
            $uris = "";
            $metaData = "";
            $first = true;
            foreach ($chunk as $track) {
                if ($first) {
                    $first = false;
                } else {
                    $uris .= " ";
                    $metaData .= " ";
                }

                $uris .= $track->getUri();
                $metaData .= $track->getMetaData();
            }

            $numberOfTracks = count($chunk);

            $data = $this->soap("AVTransport", "AddMultipleURIsToQueue", [
                "UpdateID"                          =>  0,
                "NumberOfURIs"                      =>  $numberOfTracks,
                "EnqueuedURIs"                      =>  $uris,
                "EnqueuedURIsMetaData"              =>  $metaData,
                "DesiredFirstTrackNumberEnqueued"   =>  $position,
                "EnqueueAsNext"                     =>  0,
            ]);
            $this->updateId = $data["NewUpdateID"];

            $position += $numberOfTracks;
        }
    }


    /**
     * Add a track to the queue.
     *
     * @param string|UriInterface $track The URI of the track to add, or an object that implements the UriInterface
     * @param ?int $position The position to insert the track in the queue (zero-based),
     *                      by default the track will be added to the end of the queue
     *
     * @return $this
     */
    public function addTrack($track, ?int $position = null): QueueInterface
    {
        # If a simple uri has been passed then convert it to a Track instance
        if (is_string($track)) {
            $track = $this->trackFactory->createFromUri($track);
        }

        if (!$track instanceof UriInterface) {
            $error = "The first argument to addTrack() should be an object that implements " . UriInterface::class;
            throw new \InvalidArgumentException($error);
        }

        if ($position === null) {
            $position = $this->getNextPosition();
        }

        $this->soap("AVTransport", "AddURIToQueue", [
            "UpdateID" => 0,
            "EnqueuedURI" => $track->getUri(),
            "EnqueuedURIMetaData" => $track->getMetaData(),
            "DesiredFirstTrackNumberEnqueued" => $position,
            "EnqueueAsNext" => 0,
        ]);

        return $this;
    }


    /**
     * Add tracks to the queue.
     *
     * @param string[]|UriInterface[] $tracks An array where each element is either the URI of the tracks to add,
     *                                          or an object that implements the UriInterface
     * @param ?int $position The position to insert the tracks in the queue (zero-based),
     *                      by default the tracks will be added to the end of the queue
     *
     * @return $this
     */
    public function addTracks(array $tracks, ?int $position = null): QueueInterface
    {
        $uris = [];
        foreach ($tracks as $track) {
            # If a simple uri has been passed then convert it to a Track instance
            if (is_string($track)) {
                $track = $this->trackFactory->createFromUri($track);
            }

            if (!$track instanceof UriInterface) {
                $error = "The tracks must contain either string URIs or objects that implement " . UriInterface::class;
                throw new \InvalidArgumentException($error);
            }

            $uris[] = $track;
        }

        $this->addUris($uris, $position);

        return $this;
    }


    /**
     * Remove a track from the queue.
     *
     * @param int $position The zero-based position of the track to remove
     *
     * @return bool
     */
    public function removeTrack(int $position): bool
    {
        return $this->removeTracks([$position]);
    }


    /**
     * Remove tracks from the queue.
     *
     * @param int[] $positions The zero-based positions of the tracks to remove
     *
     * @return bool
     */
    public function removeTracks(array $positions): bool
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
     * @return $this
     */
    public function clear(): QueueInterface
    {
        $this->soap("AVTransport", "RemoveAllTracksFromQueue");

        return $this;
    }
}
