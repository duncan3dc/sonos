<?php

namespace duncan3dc\Sonos\Test;

use duncan3dc\Sonos\Speaker;
use Mockery;

class SpeakerTest extends MockTest
{
    public function tearDown()
    {
        Mockery::close();
    }


    public function testGetVolume()
    {
        $device = $this->getDevice();
        $speaker = $this->getSpeaker($device);

        $device->shouldReceive("soap")->once()->with("RenderingControl", "GetVolume", [
            "Channel"   =>  "Master",
        ])->andReturn("3");
        $this->assertSame(3, $speaker->getVolume());
    }


    public function testSetVolume()
    {
        $device = $this->getDevice();
        $speaker = $this->getSpeaker($device);

        $device->shouldReceive("soap")->once()->with("RenderingControl", "SetVolume", [
            "Channel"       =>  "Master",
            "DesiredVolume" =>  10,
        ]);
        $this->assertSame($speaker, $speaker->setVolume(10));
    }


    public function testAdjustVolume()
    {
        $device = $this->getDevice();
        $speaker = $this->getSpeaker($device);

        $device->shouldReceive("soap")->once()->with("RenderingControl", "SetRelativeVolume", [
            "Channel"       =>  "Master",
            "Adjustment"    =>  5,
        ]);
        $this->assertSame($speaker, $speaker->adjustVolume(5));
    }


    public function testisMuted()
    {
        $device = $this->getDevice();
        $speaker = $this->getSpeaker($device);

        $device->shouldReceive("soap")->once()->with("RenderingControl", "GetMute", [
            "Channel"   =>  "Master",
        ])->andReturn(true);
        $this->assertSame(true, $speaker->isMuted());
    }


    public function testMute()
    {
        $device = $this->getDevice();
        $speaker = $this->getSpeaker($device);

        $device->shouldReceive("soap")->once()->with("RenderingControl", "SetMute", [
            "Channel"       =>  "Master",
            "DesiredMute"   =>  1,
        ]);
        $this->assertSame($speaker, $speaker->mute());
    }


    public function testUnmute()
    {
        $device = $this->getDevice();
        $speaker = $this->getSpeaker($device);

        $device->shouldReceive("soap")->once()->with("RenderingControl", "SetMute", [
            "Channel"       =>  "Master",
            "DesiredMute"   =>  0,
        ]);
        $this->assertSame($speaker, $speaker->unmute());
    }


    public function testGetIndicator()
    {
        $device = $this->getDevice();
        $speaker = $this->getSpeaker($device);

        $device->shouldReceive("soap")->once()->with("DeviceProperties", "GetLEDState", [])->andReturn(false);
        $this->assertSame(false, $speaker->getIndicator());
    }


    public function testSetIndicatorOn()
    {
        $device = $this->getDevice();
        $speaker = $this->getSpeaker($device);

        $device->shouldReceive("soap")->once()->with("DeviceProperties", "SetLEDState", [
            "DesiredLEDState"   =>  "On",
        ]);
        $this->assertSame($speaker, $speaker->setIndicator(true));
    }


    public function testSetIndicatorOff()
    {
        $device = $this->getDevice();
        $speaker = $this->getSpeaker($device);

        $device->shouldReceive("soap")->once()->with("DeviceProperties", "SetLEDState", [
            "DesiredLEDState"   =>  "Off",
        ]);
        $this->assertSame($speaker, $speaker->setIndicator(false));
    }
}
