<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\ControllerState;
use Mockery;

class ControllerTest extends MockTest
{

    public function testPlay()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Play", [
            "Speed" =>  1,
        ]);

        $this->assertSame($controller, $controller->play());
    }


    public function testPlayEmptyQueue()
    {
        if (defined("HHVM_VERSION")) {
            $this->markTestSkipped("Unable to mock Exceptions on HHVM");
        }

        $device = $this->getDevice();
        $controller = $this->getController($device);
        $exception = Mockery::mock("duncan3dc\Sonos\Exceptions\SoapException");

        $device->shouldReceive("soap")->once()->with("AVTransport", "Play", [
            "Speed" =>  1,
        ])->andThrow($exception);
        $device->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  1,
            "Filter"            =>  "",
            "SortCriteria"      =>  "",
            "ObjectID"          =>  "Q:0",
        ]);

        $this->setExpectedException("\BadMethodCallException");
        $controller->play();
    }



    public function testSelectTrack()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "TRACK_NR",
            "Target"    =>  4,
        ]);

        $this->assertSame($controller, $controller->selectTrack(3));
    }


    public function testSeekSeconds()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "REL_TIME",
            "Target"    =>  "00:00:55",
        ]);

        $this->assertSame($controller, $controller->seek(55));
    }


    public function testSeekMinutes()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "REL_TIME",
            "Target"    =>  "00:02:02",
        ]);

        $this->assertSame($controller, $controller->seek(122));
    }


    public function testSeekHours()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "REL_TIME",
            "Target"    =>  "01:05:00",
        ]);

        $this->assertSame($controller, $controller->seek(3900));
    }


    public function testSeekZero()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "REL_TIME",
            "Target"    =>  "00:00:00",
        ]);

        $this->assertSame($controller, $controller->seek(0));
    }


    public function testRestoreState()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $state = Mockery::mock(ControllerState::class);
        $state->speakers = [];
        $state->tracks = [];

        $device->shouldReceive("soap")->once()->with("AVTransport", "RemoveAllTracksFromQueue", ["ObjectID" => "Q:0"]);
        $device->shouldReceive("soap")->once()->with("AVTransport", "GetTransportSettings", []);
        $device->shouldReceive("soap")->once()->with("AVTransport", "SetCrossfadeMode", ["CrossfadeMode" => false]);

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetTransportInfo", []);
        $device->shouldReceive("soap")->once()->with("AVTransport", "GetTransportSettings", []);

        $controller->restoreState($state);
    }


    public function testRestoreStateWithTracks()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $state = Mockery::mock(ControllerState::class);
        $state->speakers = [];
        $state->tracks = ["track"];
        $state->position = "05:03:01";

        $device->shouldReceive("soap")->once()->with("AVTransport", "RemoveAllTracksFromQueue", ["ObjectID" => "Q:0"]);
        $device->shouldReceive("soap")->once()->with("AVTransport", "GetTransportSettings", []);
        $device->shouldReceive("soap")->once()->with("AVTransport", "SetCrossfadeMode", ["CrossfadeMode" => false]);

        $device->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  1,
            "Filter"            =>  "",
            "SortCriteria"      =>  "",
            "ObjectID"          =>  "Q:0",
        ]);
        $device->shouldReceive("soap")->once()->with("AVTransport", "AddMultipleURIsToQueue", Mockery::any());
        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "TRACK_NR",
            "Target"    =>  1,
        ]);
        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "REL_TIME",
            "Target"    =>  "05:03:01",
        ]);

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetTransportInfo", []);
        $device->shouldReceive("soap")->once()->with("AVTransport", "GetTransportSettings", []);

        $controller->restoreState($state);
    }
}
