<?php

namespace duncan3dc\Sonos\Test;

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
}
