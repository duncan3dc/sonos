<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;

class Playlist
{
    protected $id = false;
    protected $name = false;
    protected $updateId = false;
    protected $speaker = false;


    public function __construct($param)
    {
        if(is_string($param)) {
            $this->id = $param;
            $this->name = false;
        } else {
            $this->id = $param->getAttribute("id");
            $this->name = $param->getTag("title")->nodeValue;
        }

        $this->updateId = false;
        $this->speaker = Network::getSpeaker();
    }


    protected function soap($service, $action, $params = [])
    {
        $params["ObjectID"] = $this->id;

        if($action == "Browse") {
            $params["Filter"] = "";
            $params["SortCriteria"] = "";
        }

        return $this->speaker->soap($service, $action, $params);
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
        if(!$this->updateId || !Network::$cache) {
            echo "getting update id for (" . $this->id . ")...\n";
            $data = $this->browse("DirectChildren");
            $this->updateId = $data["UpdateID"];
        }
        return $this->updateId;
    }


    public function getName()
    {
        if(!$this->name) {
            $data = $this->browse("Metadata");
            $xml = new XmlParser($data["Result"]);
            $this->name = $xml->getTag("title")->nodeValue;
        }
        return $this->name;
    }


    public function getTracks()
    {
        $items = [];

        $start = 0;
        $limit = 100;
        do {
            $data = $this->browse("DirectChildren", $start, $limit);
            $parser = new XmlParser($data["Result"]);
            foreach($parser->getTags("item") as $item) {
                $items[] = [
                    "id"        =>  $item->getAttribute("id"),
                    "uri"       =>  $item->getTag("res")->nodeValue,
                    "title"     =>  $item->getTag("title")->nodeValue,
                    "artist"    =>  $item->getTag("creator")->nodeValue,
                    "album"     =>  $item->getTag("album")->nodeValue,
                ];
            }

            $start += $limit;
        } while($data["TotalMatches"] && count($items) < $data["TotalMatches"]);

        return $items;
    }


    public function addTracks($tracks, $position = null)
    {
        if($position === null) {
            $position = $data["TotalMatches"];
        }

        if(!is_array($tracks)) {
            $tracks = [$tracks];
        }

        # Ensure the update id is set to begin with
        $this->getUpdateID();

        foreach($tracks as $uri) {
            $data = $this->soap("AVTransport", "AddURIToSavedQueue", [
                "UpdateID"              =>  $this->updateId,
                "EnqueuedURI"           =>  $uri,
                "EnqueuedURIMetaData"   =>  "",
                "AddAtIndex"            =>  $position++,
            ]);
            $this->updateId = $data["NewUpdateID"];

            if($data["NumTracksAdded"] != 1) {
                return false;
            }
        }
        return true;
    }


    public function removeTracks($positions)
    {
        if(!is_array($positions)) {
            $positions = [$positions];
        }

        $data = $this->soap("AVTransport", "ReorderTracksInSavedQueue", [
            "UpdateID"              =>  $this->getUpdateID(),
            "TrackList"             =>  implode(",", $positions),
            "NewPositionList"       =>  "",
        ]);
        $this->updateId = $data["NewUpdateID"];

        return ($data["QueueLengthChange"] == (count($positions) * -1));
    }
}
