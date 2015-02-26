<?php

namespace duncan3dc\Sonos\Test;

use duncan3dc\Sonos\Network;

class NetworkLiveTest extends LiveTest
{

    public function testGetSpeakers()
    {
        $result = $this->network->getSpeakers();
        $this->assertContainsOnlyInstancesOf("duncan3dc\\Sonos\\Speaker", $result);
    }


    public function testGetController()
    {
        $result = $this->network->getController();
        $this->assertInstanceOf("duncan3dc\\Sonos\\Controller", $result);
    }


    public function testGetSpeakerByRoom1()
    {
        $result = $this->network->getSpeakerByRoom("Kitchen");
        $this->assertInstanceOf("duncan3dc\\Sonos\\Speaker", $result);
    }


    public function testGetSpeakerByRoom2()
    {
        $result = $this->network->getSpeakerByRoom("No such room");
        $this->assertNull($result);
    }


    public function testGetSpeakersByRoom1()
    {
        $result = $this->network->getSpeakersByRoom("Kitchen");
        $this->assertContainsOnlyInstancesOf("duncan3dc\\Sonos\\Speaker", $result);
    }


    public function testGetSpeakersByRoom2()
    {
        $result = $this->network->getSpeakersByRoom("No such room");
        $this->assertSame([], $result);
    }


    public function testGetControllers()
    {
        $result = $this->network->getControllers();
        $this->assertContainsOnlyInstancesOf("duncan3dc\\Sonos\\Controller", $result);
    }


    public function testGetControllerByRoom1()
    {
        $result = $this->network->getControllerByRoom("Kitchen");
        $this->assertInstanceOf("duncan3dc\\Sonos\\Controller", $result);
    }


    public function testGetControllerByRoom2()
    {
        $result = $this->network->getControllerByRoom("No such room");
        $this->assertNull($result);
    }


    public function testGetPlaylists()
    {
        $result = $this->network->getPlaylists();
        $this->assertContainsOnlyInstancesOf("duncan3dc\\Sonos\\Playlist", $result);
    }


    public function testGetPlaylistByName()
    {
        $result = $this->network->getPlaylistByName("No such playlist");
        $this->assertNull($result);
    }


    public function testGetAlarms()
    {
        $result = $this->network->getAlarms();
        $this->assertContainsOnlyInstancesOf("duncan3dc\\Sonos\\Alarm", $result);
    }
}
