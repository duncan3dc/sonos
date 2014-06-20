<?php

namespace duncan3dc\Sonos;
use \duncan3dc\DomParser\XmlParser;

class Controller extends Speaker {

    const STATE_STOPPED         =   201;
    const STATE_PLAYING         =   202;
    const STATE_PAUSED          =   203;
    const STATE_TRANSITIONING   =   204;
    const STATE_UNKNOWN         =   205;


    public function __construct(Speaker $speaker) {

        $this->ip = $speaker->ip;

        $this->name = $speaker->name;
        $this->room = $speaker->room;
        $this->group = $speaker->getGroup();
        $this->uuid = $speaker->getUuid();

    }


    public function isCoordinator() {
        return true;
    }


    public function getStateName() {
        $data = $this->soap("AVTransport","GetTransportInfo");
        return $data["CurrentTransportState"];
    }


    public function getState() {
        $name = $this->getStateName();
        switch($name) {
            case "STOPPED":
                $state = self::STATE_STOPPED;
                break;
            case "PLAYING":
                $state = self::STATE_PLAYING;
                break;
            case "PAUSED_PLAYBACK":
                $state = self::STATE_PAUSED;
                break;
            case "TRANSITIONING":
                $state = self::STATE_TRANSITIONING;
                break;
            default:
                $state = self::STATE_UNKNOWN;
                break;
        }
        return $state;
    }


    public function getStateDetails() {
        $data = $this->soap("AVTransport","GetPositionInfo");
        $meta = $this->getTrackMetaData($data["TrackMetaData"]);
        return array_merge($meta,[
            "queue-number"  =>  $data["Track"],
            "duration"      =>  $data["TrackDuration"],
            "position"      =>  $data["RelTime"],
        ]);
    }


    protected function getTrackMetaData($xml) {
        if(is_object($xml)) {
            $parser = $xml;
        } elseif($xml) {
            $parser = new XmlParser($xml);
        } else {
            return [];
        }
        return [
            "title"         =>  $parser->getTag("title")->nodeValue,
            "artist"        =>  $parser->getTag("creator")->nodeValue,
            "album"         =>  $parser->getTag("album")->nodeValue,
            "track-number"  =>  $parser->getTag("originalTrackNumber")->nodeValue,
        ];
    }


    public function setState($state) {
        switch($state) {
            case self::STATE_PLAYING:
                return $this->play();
            break;
            case self::STATE_PAUSED:
                return $this->pause();
            break;
            case self::STATE_STOPPED;
                return $this->pause();
            break;
            default:
                throw new \Exception("Unknown state (" . $state . ")");
            break;
        }
    }


    public function play() {
        return $this->soap("AVTransport","Play",[
            "Speed"         =>  1,
        ]);
    }


    public function pause() {
        return $this->soap("AVTransport","Pause");
    }


    public function next() {
        return $this->soap("AVTransport","Next");
    }


    public function previous() {
        return $this->soap("AVTransport","Previous");
    }


    public function getSpeakers() {
        $group = [];
        $speakers = Network::getSpeakers();
        foreach($speakers as $speaker) {
            if($speaker->getGroup() == $this->getGroup()) {
                $group[] = $speaker;
            }
        }
        return $group;
    }


    public function addSpeaker(Speaker $speaker) {
        if($speaker->getUuid() == $this->getUuid()) {
            return;
        }
        $speaker->soap("AVTransport","SetAVTransportURI",[
            "CurrentURI"            =>  "x-rincon:" . $this->getUuid(),
            "CurrentURIMetaData"    =>  "",
        ]);
    }


    public function removeSpeaker(Speaker $speaker) {
        $speaker->soap("AVTransport","BecomeCoordinatorOfStandaloneGroup");
    }


    public function setVolume($volume) {
        $speakers = $this->getSpeakers();
        foreach($speakers as $speaker) {
            $speaker->setVolume($volume);
        }
    }


    public function adjustVolume($adjust) {
        $speakers = $this->getSpeakers();
        foreach($speakers as $speaker) {
            $speaker->adjustVolume($adjust);
        }
    }


    public function getMode() {
        $data = $this->soap("AVTransport","GetTransportSettings");
        $options = [
            "shuffle"   =>  false,
            "repeat"    =>  false,
        ];
        if(in_array($data["PlayMode"],["REPEAT_ALL","SHUFFLE"])) {
            $options["repeat"] = true;
        }
        if(in_array($data["PlayMode"],["SHUFFLE_NOREPEAT","SHUFFLE"])) {
            $options["shuffle"] = true;
        }
        return $options;
    }


    public function setMode($options) {
        if($options["shuffle"]) {
            if($options["repeat"]) {
                $mode = "SHUFFLE";
            } else {
                $mode = "SHUFFLE_NOREPEAT";
            }
        } else {
            if($options["repeat"]) {
                $mode = "REPEAT_ALL";
            } else {
                $mode = "NORMAL";
            }
        }
        $data = $this->soap("AVTransport","SetPlayMode",[
            "NewPlayMode"   =>  $mode,
        ]);
    }


    public function getRepeat() {
        $mode = $this->getMode();
        return $mode["repeat"];
    }


    public function setRepeat($repeat) {
        $mode = $this->getMode();
        if($mode["repeat"] == $repeat) {
            return;
        }

        $mode["repeat"] = $repeat;
        $this->setMode($mode);
    }


    public function getShuffle() {
        $mode = $this->getMode();
        return $mode["shuffle"];
    }


    public function setShuffle($shuffle) {
        $mode = $this->getMode();
        if($mode["shuffle"] == $shuffle) {
            return;
        }

        $mode["shuffle"] = $shuffle;
        $this->setMode($mode);
    }


    public function getQueue($start=0,$limit=100) {
        if($start < 0) {
            $limit += $start;
            $start = 0;
        }
        if($limit < 1) {
            return [];
        }
        $data = $this->soap("ContentDirectory","Browse",[
            "ObjectID"          =>  "Q:0",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "",
            "StartingIndex"     =>  $start,
            "RequestedCount"    =>  $limit,
            "SortCriteria"      =>  "",
        ]);
        $parser = new XmlParser($data["Result"]);
        $queue = [];
        foreach($parser->getTags("item") as $item) {
            $queue[] = $this->getTrackMetaData($item);
        }
        return $queue;
    }


}
