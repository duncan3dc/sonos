<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\Alarm;
use duncan3dc\Sonos\Controller;
use duncan3dc\Sonos\Playlist;
use duncan3dc\Sonos\Speaker;

class NetworkLiveTest extends LiveTest
{

    public function testGetSpeakers()
    {
        $result = $this->network->getSpeakers();
        $this->assertContainsOnlyInstancesOf(Speaker::class, $result);
    }


    public function testGetController()
    {
        $result = $this->network->getController();
        $this->assertInstanceOf(Controller::class, $result);
    }


    public function testGetSpeakerByRoom1()
    {
        $result = $this->network->getSpeakerByRoom("Kitchen");
        $this->assertInstanceOf(Speaker::class, $result);
    }


    public function testGetSpeakerByRoom2()
    {
        $result = $this->network->getSpeakerByRoom("No such room");
        $this->assertNull($result);
    }


    public function testGetSpeakersByRoom1()
    {
        $result = $this->network->getSpeakersByRoom("Kitchen");
        $this->assertContainsOnlyInstancesOf(Speaker::class, $result);
    }


    public function testGetSpeakersByRoom2()
    {
        $result = $this->network->getSpeakersByRoom("No such room");
        $this->assertSame([], $result);
    }


    public function testGetControllers()
    {
        $result = $this->network->getControllers();
        $this->assertContainsOnlyInstancesOf(Controller::class, $result);
    }


    public function testGetControllerByRoom1()
    {
        $result = $this->network->getControllerByRoom("Kitchen");
        $this->assertInstanceOf(Controller::class, $result);
    }


    public function testGetControllerByRoom2()
    {
        $result = $this->network->getControllerByRoom("No such room");
        $this->assertNull($result);
    }


    public function testGetPlaylists()
    {
        $result = $this->network->getPlaylists();
        $this->assertContainsOnlyInstancesOf(Playlist::class, $result);
    }


    public function testGetPlaylistByName()
    {
        $result = $this->network->getPlaylistByName("No such playlist");
        $this->assertNull($result);
    }


    public function testGetAlarms()
    {
        $result = $this->network->getAlarms();
        $this->assertContainsOnlyInstancesOf(Alarm::class, $result);
    }
}
