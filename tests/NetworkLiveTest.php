<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\Alarm;
use duncan3dc\Sonos\Controller;
use duncan3dc\Sonos\Exceptions\NotFoundException;
use duncan3dc\Sonos\Playlist;
use duncan3dc\Sonos\Speaker;

class NetworkLiveTest extends LiveTest
{
    public function testGetSpeakers(): void
    {
        $result = $this->network->getSpeakers();
        $this->assertContainsOnlyInstancesOf(Speaker::class, $result);
    }


    public function testGetController(): void
    {
        $result = $this->network->getController();
        $this->assertInstanceOf(Controller::class, $result);
    }


    public function testGetSpeakerByRoom1(): void
    {
        $result = $this->network->getSpeakerByRoom("Kitchen");
        $this->assertInstanceOf(Speaker::class, $result);
    }


    public function testGetSpeakerByRoom2(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Unable to find a speaker for the room 'No such room'");
        $this->network->getSpeakerByRoom("No such room");
    }


    public function testGetSpeakersByRoom1(): void
    {
        $result = $this->network->getSpeakersByRoom("Kitchen");
        $this->assertContainsOnlyInstancesOf(Speaker::class, $result);
    }


    public function testGetSpeakersByRoom2(): void
    {
        $result = $this->network->getSpeakersByRoom("No such room");
        $this->assertSame([], $result);
    }


    public function testGetControllers(): void
    {
        $result = $this->network->getControllers();
        $this->assertContainsOnlyInstancesOf(Controller::class, $result);
    }


    public function testGetControllerByRoom1(): void
    {
        $result = $this->network->getControllerByRoom("Kitchen");
        $this->assertInstanceOf(Controller::class, $result);
    }
    public function testGetControllerByRoom2(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Unable to find a speaker for the room 'No such room'");
        $this->network->getControllerByRoom("No such room");
    }


    public function testGetControllerByIp1(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Unable to find a speaker for the IP address '999.999.999.999'");
        $this->network->getControllerByIp("999.999.999.999");
    }


    public function testGetPlaylists(): void
    {
        $result = $this->network->getPlaylists();
        $this->assertContainsOnlyInstancesOf(Playlist::class, $result);
    }


    public function testGetPlaylistByName(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("No playlist called 'No such playlist' exists on this network");
        $this->network->getPlaylistByName("No such playlist");
    }


    public function testGetAlarms(): void
    {
        $result = $this->network->getAlarms();
        $this->assertContainsOnlyInstancesOf(Alarm::class, $result);
    }
    public function testGetAlarmById(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Unable to find an alarm with the id -9 on this network");
        $this->network->getAlarmById(-9);
    }
}
