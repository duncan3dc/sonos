<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Tracks\Track;
use duncan3dc\Sonos\Tracks\Factory as TrackFactory;
use duncan3dc\Sonos\Tracks\UriInterface;

/**
 * Provides an interface for managing the favourites.
 */
class Favourites implements \Countable
{

    /**
     * @var Controller $controller The Controller instance to communicate with.
     */
    private $controller;

    /**
     * @var TrackFactory $trackFactory A factory to create tracks from.
     */
    private $trackFactory;


    /**
     * Create an instance of the Favourites class.
     *
     * @param Controller $controller The Controller instance to communicate with
     */
    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
        $this->trackFactory = new TrackFactory($this->controller);
    }


    /**
     * Send a browse request to the controller to get favourites info.
     *
     * @param int $start The position to start browsing from
     * @param int $limit The number of tracks to return
     *
     * @return mixed
     */
    private function browse($start = 0, $limit = 1)
    {
        return $this->controller->soap("ContentDirectory", "Browse", [
            "ObjectID"          =>  "FV:2",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "*",
            "StartingIndex"     =>  $start,
            "RequestedCount"    =>  $limit,
            "SortCriteria"      =>  "",
        ]);
    }


    /**
     * The the number of tracks in the favourites.
     *
     * @return int
     */
    public function count()
    {
        $data = $this->browse();
        return (int) $data["TotalMatches"];
    }


    /**
     * Get tracks from the favourites.
     *
     * @param int $start The zero-based position to start from
     * @param int $total The maximum number of tracks to return
     *
     * @return Track[]
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
            $data = $this->browse($start, $limit);
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
}
