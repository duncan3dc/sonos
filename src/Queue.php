<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;

class Queue
{
    protected $id = false;
    protected $updateId = false;
    protected $controller = false;


    public function __construct(Controller $param)
    {
        $this->id = "Q:0";
        $this->updateId = false;
        $this->controller = $param;
    }


    protected function soap($service, $action, $params = [])
    {
        $params["ObjectID"] = $this->id;

        if ($action == "Browse") {
            $params["Filter"] = "";
            $params["SortCriteria"] = "";
        }

        return $this->controller->soap($service, $action, $params);
    }


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


    protected function getUpdateId()
    {
        if (!$this->updateId || !Network::$cache) {
            $data = $this->browse("DirectChildren");
            $this->updateId = $data["UpdateID"];
        }
        return $this->updateId;
    }


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
                $tracks[] = [
                    "id"        =>  $item->getAttribute("id"),
                    "uri"       =>  $item->getTag("res")->nodeValue,
                    "title"     =>  $item->getTag("title")->nodeValue,
                    "artist"    =>  $item->getTag("creator")->nodeValue,
                    "album"     =>  $item->getTag("album")->nodeValue,
                ];
                if ($total > 0 && count($tracks) >= $total) {
                    return $tracks;
                }
            }

            $start += $limit;
        } while ($data["TotalMatches"] && count($tracks) < $data["TotalMatches"]);

        return $tracks;
    }


    public function addTracks($tracks, $position = null)
    {
        if ($position === null) {
            $data = $this->browse("DirectChildren");
            $this->updateId = $data["UpdateID"];
            $position = $data["TotalMatches"] + 1;
        }

        if (!is_array($tracks)) {
            $tracks = [$tracks];
        }

        # Ensure the update id is set to begin with
        $this->getUpdateID();

        foreach ($tracks as $uri) {
            $data = $this->soap("AVTransport", "AddURIToQueue", [
                "UpdateID"                          =>  $this->updateId,
                "EnqueuedURI"                       =>  $uri,
                "EnqueuedURIMetaData"               =>  "",
                "DesiredFirstTrackNumberEnqueued"   =>  $position++,
                "EnqueueAsNext"                     =>  0,
            ]);
            $this->updateId = $data["NewUpdateID"];

            if ($data["NumTracksAdded"] != 1) {
                return false;
            }
        }
        return true;
    }


    public function removeTracks($positions)
    {
        if (!is_array($positions)) {
            $positions = [$positions];
        }

        $ranges = [];
        $key = 0;
        $last = -1;
        sort($positions);
        foreach ($positions as $position) {
            $position++;
            if ($last > -1) {
                if ($position == $last + 1) {
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
}
