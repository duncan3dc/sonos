<?php

namespace duncan3dc\Sonos\Test;

use duncan3dc\Sonos\Network;

class NetworkTest extends LiveTest
{

    public function testGetSpeakers()
    {
        $this->assertContainsOnlyInstancesOf("duncan3dc\\Sonos\\Speaker", $this->network->getSpeakers());
    }


    public function testGetController()
    {
        $this->assertInstanceOf("duncan3dc\\Sonos\\Controller", $this->network->getController());
    }


    public function testGetSpeakerByRoom1()
    {
        $this->assertInstanceOf("duncan3dc\\Sonos\\Speaker", $this->network->getSpeakerByRoom("Kitchen"));
    }


    public function testGetSpeakerByRoom2()
    {
        $this->setExpectedException("InvalidArgumentException");
        $this->network->getSpeakerByRoom("No such room");
    }


    public function testGetSpeakersByRoom1()
    {
        $this->assertContainsOnlyInstancesOf("duncan3dc\\Sonos\\Speaker", $this->network->getSpeakersByRoom("Kitchen"));
    }


    public function testGetSpeakersByRoom2()
    {
        $this->setExpectedException("InvalidArgumentException");
        $this->network->getSpeakersByRoom("No such room");
    }


    public function testGetControllers()
    {
        $this->assertContainsOnlyInstancesOf("duncan3dc\\Sonos\\Controller", $this->network->getControllers());
    }


    public function testGetControllerByRoom1()
    {
        $this->assertInstanceOf("duncan3dc\\Sonos\\Controller", $this->network->getControllerByRoom("Kitchen"));
    }


    public function testGetControllerByRoom2()
    {
        $this->setExpectedException("InvalidArgumentException");
        $this->network->getControllerByRoom("No such room");
    }


    public function testGetPlaylists()
    {
        $this->assertContainsOnlyInstancesOf("duncan3dc\\Sonos\\Playlist", $this->network->getPlaylists());
    }


    public function testGetPlaylistByName()
    {
        $this->setExpectedException("InvalidArgumentException");
        $this->network->getPlaylistByName("No such playlist");
    }


    public function testGetAlarms()
    {
        $this->assertContainsOnlyInstancesOf("duncan3dc\\Sonos\\Alarm", $this->network->getAlarms());
    }
}
