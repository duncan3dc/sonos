<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\Exceptions\UnknownGroupException;
use duncan3dc\Sonos\Speaker;
use Mockery;

class SpeakerTest extends MockTest
{
    protected $device;
    protected $speaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->device = $this->getDevice();
        $this->speaker = $this->getSpeaker($this->device);
    }

    public function tearDown(): void
    {
        Mockery::close();
    }


    public function testGetVolume(): void
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "GetVolume", [
            "Channel"   =>  "Master",
        ])->andReturn("3");

        $this->assertSame(3, $this->speaker->getVolume());
    }


    public function testSetVolume(): void
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetVolume", [
            "Channel"       =>  "Master",
            "DesiredVolume" =>  10,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setVolume(10));
    }


    public function testAdjustVolume(): void
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetRelativeVolume", [
            "Channel"       =>  "Master",
            "Adjustment"    =>  5,
        ]);

        $this->assertSame($this->speaker, $this->speaker->adjustVolume(5));
    }


    public function testisMuted(): void
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "GetMute", [
            "Channel"   =>  "Master",
        ])->andReturn(true);

        $this->assertSame(true, $this->speaker->isMuted());
    }


    public function testMute(): void
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetMute", [
            "Channel"       =>  "Master",
            "DesiredMute"   =>  1,
        ]);

        $this->assertSame($this->speaker, $this->speaker->mute());
    }


    public function testUnmute(): void
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetMute", [
            "Channel"       =>  "Master",
            "DesiredMute"   =>  0,
        ]);

        $this->assertSame($this->speaker, $this->speaker->unmute());
    }


    public function testGetIndicator(): void
    {
        $this->device->shouldReceive("soap")->once()->with("DeviceProperties", "GetLEDState", [])->andReturn(false);

        $this->assertSame(false, $this->speaker->getIndicator());
    }


    public function testSetIndicatorOn(): void
    {
        $this->device->shouldReceive("soap")->once()->with("DeviceProperties", "SetLEDState", [
            "DesiredLEDState"   =>  "On",
        ]);

        $this->assertSame($this->speaker, $this->speaker->setIndicator(true));
    }


    public function testSetIndicatorOff(): void
    {
        $this->device->shouldReceive("soap")->once()->with("DeviceProperties", "SetLEDState", [
            "DesiredLEDState"   =>  "Off",
        ]);

        $this->assertSame($this->speaker, $this->speaker->setIndicator(false));
    }


    public function testGetTreble(): void
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "GetTreble", [
            "Channel"   =>  "Master",
        ])->andReturn(8);

        $this->assertSame(8, $this->speaker->getTreble());
    }


    public function testSetTreble(): void
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetTreble", [
            "Channel"       =>  "Master",
            "DesiredTreble" =>  7,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setTreble(7));
    }


    public function testSetTrebleHigh(): void
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetTreble", [
            "Channel"       =>  "Master",
            "DesiredTreble" =>  10,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setTreble(15));
    }


    public function testSetTrebleLow(): void
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetTreble", [
            "Channel"       =>  "Master",
            "DesiredTreble" =>  -10,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setTreble(-11));
    }


    public function testSetBass(): void
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetBass", [
            "Channel"       =>  "Master",
            "DesiredBass"   =>  0,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setBass(0));
    }


    public function testSetBassHigh(): void
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetBass", [
            "Channel"       =>  "Master",
            "DesiredBass"   =>  10,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setBass(11));
    }


    public function testSetBassLow(): void
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetBass", [
            "Channel"       =>  "Master",
            "DesiredBass"   =>  -10,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setBass(-100));
    }


    public function testGetLoudness(): void
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "GetLoudness", [
            "Channel"           =>  "Master",
        ])->andReturn(false);

        $this->assertSame(false, $this->speaker->getLoudness());
    }


    public function testSetLoudnessOn(): void
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetLoudness", [
            "Channel"           =>  "Master",
            "DesiredLoudness"   =>  1,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setLoudness(true));
    }


    public function testSetLoudnessOff(): void
    {
        $this->device->shouldReceive("soap")->once()->with("RenderingControl", "SetLoudness", [
            "Channel"           =>  "Master",
            "DesiredLoudness"   =>  0,
        ]);

        $this->assertSame($this->speaker, $this->speaker->setLoudness(false));
    }


    public function testIsCoordinator1(): void
    {
        $device = $this->getDevice();

        $device->shouldReceive("isSpeaker")->once()->andReturn(true);
        $device->shouldReceive("soap")->with("ZoneGroupTopology", "GetZoneGroupAttributes", [])->andReturn([
            "CurrentZoneGroupID" => "RINCON_5CAAFD472E1C01400:195",
            "CurrentZonePlayerUUIDsInGroup" => "RINCON_5CAAFD472E1C01400",
        ]);

        $speaker = new Speaker($device);

        $this->assertTrue($speaker->isCoordinator());
    }
    public function testIsCoordinator2(): void
    {
        $device = $this->getDevice();

        $device->shouldReceive("isSpeaker")->once()->andReturn(true);
        $device->shouldReceive("soap")->with("ZoneGroupTopology", "GetZoneGroupAttributes", [])->andReturn([
            "CurrentZoneGroupID" => "RINCON_5CAAFD472E1C01400:195",
            "CurrentZonePlayerUUIDsInGroup" => "RINCON_5CAAF0000A1A01400",
        ]);

        $speaker = new Speaker($device);

        $this->assertTrue($speaker->isCoordinator());
    }
    public function testIsCoordinator3(): void
    {
        $device = $this->getDevice();

        $device->shouldReceive("isSpeaker")->once()->andReturn(true);
        $device->shouldReceive("soap")->with("ZoneGroupTopology", "GetZoneGroupAttributes", [])->andReturn([
            "CurrentZoneGroupID" => "RINCON_5CAAFD472E1C01400:195",
            "CurrentZonePlayerUUIDsInGroup" => "RINCON_5CAAFD472E1C01400,RINCON_5CAAF0000A1A01400",
        ]);

        $speaker = new Speaker($device);

        $this->assertTrue($speaker->isCoordinator());
    }
    public function testIsCoordinator4(): void
    {
        $device = $this->getDevice();

        $device->shouldReceive("isSpeaker")->once()->andReturn(true);
        $device->shouldReceive("soap")->with("ZoneGroupTopology", "GetZoneGroupAttributes", [])->andReturn([
            "CurrentZoneGroupID" => "RINCON_5CAAF0000A1A01400:195",
            "CurrentZonePlayerUUIDsInGroup" => "RINCON_5CAAFD472E1C01400,RINCON_5CAAF0000A1A01400",
        ]);

        $speaker = new Speaker($device);

        $this->assertFalse($speaker->isCoordinator());
    }


    public function testGetGroup(): void
    {
        $device = $this->getDevice();

        $device->shouldReceive("isSpeaker")->once()->andReturn(true);
        $device->shouldReceive("soap")->with("ZoneGroupTopology", "GetZoneGroupAttributes", [])->andReturn([
            "CurrentZoneGroupID" => "",
            "CurrentZonePlayerUUIDsInGroup" => "RINCON_5CAAFD472E1C01400",
        ]);

        $speaker = new Speaker($device);

        $this->expectException(UnknownGroupException::class);
        $this->expectExceptionMessage("Unable to figure out the group of this speaker");
        $speaker->getGroup();
    }
}
