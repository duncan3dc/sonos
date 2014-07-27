<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;

class Playlist extends Queue
{
    protected $name = false;
    protected $type = "SavedQueue";


    public static function create($name)
    {
        $controller = Network::getController();

        $data = $controller->soap("AVTransport", "CreateSavedQueue", [
            "Title"                 =>  $name,
            "EnqueuedURI"           =>  "",
            "EnqueuedURIMetaData"   =>  "",
        ]);
        return new static($data["AssignedObjectID"]);
    }


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
        $this->controller = Network::getController();
    }


    public function getId()
    {
        return $this->id;
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


    public function delete()
    {
        $this->soap("ContentDirectory", "DestroyObject");
        return true;
    }
}
