<?php

namespace duncan3dc\Sonos\Test;

use duncan3dc\Sonos\Network;

class NetworkTest extends SonosTest
{

    public function testGetSpeakers()
    {
        $this->assertContainsOnlyInstancesOf("duncan3dc\\Sonos\\Speaker", Network::getSpeakers());
    }


    public function testGetController()
    {
        $this->assertInstanceOf("duncan3dc\\Sonos\\Controller", Network::getController());
    }


    public function testGetSpeakerByRoom1()
    {
        $this->assertInstanceOf("duncan3dc\\Sonos\\Speaker", Network::getSpeakerByRoom("Kitchen"));
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetSpeakerByRoom2()
    {
        Network::getSpeakerByRoom("No such room");
    }


    public function testGetSpeakersByRoom1()
    {
        $this->assertContainsOnlyInstancesOf("duncan3dc\\Sonos\\Speaker", Network::getSpeakersByRoom("Kitchen"));
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetSpeakersByRoom2()
    {
        Network::getSpeakersByRoom("No such room");
    }


    public function testGetControllers()
    {
        $this->assertContainsOnlyInstancesOf("duncan3dc\\Sonos\\Controller", Network::getControllers());
    }


    public function testGetControllerByRoom1()
    {
        $this->assertInstanceOf("duncan3dc\\Sonos\\Controller", Network::getControllerByRoom("Kitchen"));
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetControllerByRoom2()
    {
        Network::getControllerByRoom("No such room");
    }


    public function testGetPlaylists()
    {
        $this->assertContainsOnlyInstancesOf("duncan3dc\\Sonos\\Playlist", Network::getPlaylists());
    }


    /**
     * @expectedException \InvalidArgumentException
     */
    public function testGetPlaylistByName()
    {
        Network::getPlaylistByName("No such playlist");
    }


    public function testGetAlarms()
    {
        $this->assertContainsOnlyInstancesOf("duncan3dc\\Sonos\\Alarm", Network::getAlarms());
    }
}
