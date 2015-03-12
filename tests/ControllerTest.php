<?php

namespace duncan3dc\Sonos\Test;

use duncan3dc\Sonos\Controller;
use duncan3dc\Sonos\Network;

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
