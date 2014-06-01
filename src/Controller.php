<?php

namespace Sonos;

class Controller extends Speaker {


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


    public function getState() {
        $data = $this->soap("AVTransport","GetTransportInfo");
        return $data["CurrentTransportState"];
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
        $speaker->soap("AVTransport","SetAVTransportURI",array(
            "CurrentURI"            =>  "x-rincon:" . $this->getUuid(),
            "CurrentURIMetaData"    =>  "",
        ));
    }


    public function removeSpeaker(Speaker $speaker) {
        if($speaker->isCoordinator()) {
            throw new \Exception("You cannot remove the coordinator from it's group");
        }
        $speaker->soap("AVTransport","SetAVTransportURI",array(
            "CurrentURI"            =>  "",
            "CurrentURIMetaData"    =>  "",
        ));
    }


}
