<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\Exceptions\UnknownGroupException;
use duncan3dc\Sonos\Speaker;
use Mockery;

class SpeakerTest extends MockTest
{
    protected $device;
    protected $speaker;

    public function setUp()
    {
        parent::setUp();

        $this->device = $this->getDevice();
        $this->speaker = $this->getSpeaker($this->device);
    }

    public function tearDown()
    {
        Mockery::close();
    }


    public function testGetVolume()
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "GetVolume", [
            "Channel"   =>  "Master",
        ])->andReturn("3");

        $this->assertSame(3, $this->speaker->getVolume());
    }


    public function testSetVolume()
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetVolume", [
            "Channel"       =>  "Master",
            "DesiredVolume" =>  10,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setVolume(10));
    }


    public function testAdjustVolume()
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetRelativeVolume", [
            "Channel"       =>  "Master",
            "Adjustment"    =>  5,
        ]);

        $this->assertSame($this->speaker, $this->speaker->adjustVolume(5));
    }


    public function testisMuted()
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "GetMute", [
            "Channel"   =>  "Master",
        ])->andReturn(true);

        $this->assertSame(true, $this->speaker->isMuted());
    }


    public function testMute()
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetMute", [
            "Channel"       =>  "Master",
            "DesiredMute"   =>  1,
        ]);

        $this->assertSame($this->speaker, $this->speaker->mute());
    }


    public function testUnmute()
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetMute", [
            "Channel"       =>  "Master",
            "DesiredMute"   =>  0,
        ]);

        $this->assertSame($this->speaker, $this->speaker->unmute());
    }


    public function testGetIndicator()
    {
        $this->device->shouldReceive("soap")->once()->with("DeviceProperties", "GetLEDState", [])->andReturn(false);

        $this->assertSame(false, $this->speaker->getIndicator());
    }


    public function testSetIndicatorOn()
    {
        $this->device->shouldReceive("soap")->once()->with("DeviceProperties", "SetLEDState", [
            "DesiredLEDState"   =>  "On",
        ]);

        $this->assertSame($this->speaker, $this->speaker->setIndicator(true));
    }


    public function testSetIndicatorOff()
    {
        $this->device->shouldReceive("soap")->once()->with("DeviceProperties", "SetLEDState", [
            "DesiredLEDState"   =>  "Off",
        ]);

        $this->assertSame($this->speaker, $this->speaker->setIndicator(false));
    }


    public function testGetTreble()
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "GetTreble", [
            "Channel"   =>  "Master",
        ])->andReturn(8);

        $this->assertSame(8, $this->speaker->getTreble());
    }


    public function testSetTreble()
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetTreble", [
            "Channel"       =>  "Master",
            "DesiredTreble" =>  7,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setTreble(7));
    }


    public function testSetTrebleHigh()
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetTreble", [
            "Channel"       =>  "Master",
            "DesiredTreble" =>  10,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setTreble(15));
    }


    public function testSetTrebleLow()
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetTreble", [
            "Channel"       =>  "Master",
            "DesiredTreble" =>  -10,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setTreble(-11));
    }


    public function testSetBass()
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetBass", [
            "Channel"       =>  "Master",
            "DesiredBass"   =>  0,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setBass(0));
    }


    public function testSetBassHigh()
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetBass", [
            "Channel"       =>  "Master",
            "DesiredBass"   =>  10,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setBass(11));
    }


    public function testSetBassLow()
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetBass", [
            "Channel"       =>  "Master",
            "DesiredBass"   =>  -10,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setBass(-100));
    }


    public function testGetLoudness()
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "GetLoudness", [
            "Channel"           =>  "Master",
        ])->andReturn(false);

        $this->assertSame(false, $this->speaker->getLoudness());
    }


    public function testSetLoudnessOn()
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetLoudness", [
            "Channel"           =>  "Master",
            "DesiredLoudness"   =>  1,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setLoudness(true));
    }


    public function testSetLoudnessOff()
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetLoudness", [
            "Channel"           =>  "Master",
            "DesiredLoudness"   =>  0,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setLoudness(false));
    }


    public function testGetGroup()
    {
        $device = $this->getDevice();

        $device->shouldReceive("isSpeaker")->once()->andReturn(true);
        $device->shouldReceive("soap")->with("ZoneGroupTopology", "GetZoneGroupAttributes", [])->andReturn([
            "CurrentZoneGroupID" => "",
        ]);

        $speaker = new Speaker($device);

        $this->expectException(UnknownGroupException::class);
        $this->expectExceptionMessage("Unable to figure out the group of this speaker");
        $speaker->getGroup();
    }
}
