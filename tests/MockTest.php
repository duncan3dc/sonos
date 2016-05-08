<?php

namespace duncan3dc\SonosTests;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Controller;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use duncan3dc\Sonos\Network;
use duncan3dc\Sonos\Speaker;
use Mockery;
use PHPUnit\Framework\TestCase;

abstract class MockTest extends TestCase
{
    protected $network;

    public function tearDown()
    {
        Mockery::close();
    }

    public function setUp()
    {
        $this->network = Mockery::mock(Network::class);

        $this->network->shouldReceive("getSpeakers")->andReturn([]);
    }

    protected function getDevice()
    {
        $device = Mockery::mock(DeviceInterface::class);
        $device->shouldReceive("getIp")->andReturn("192.168.0.66");

        $parser = Mockery::mock(XmlParser::class);
        $tag = Mockery::mock(XmlParser::class);
        $parser->shouldReceive("getTag")->with("device")->once()->andReturn($tag);
        $tag->shouldReceive("getTag")->with("friendlyName")->once()->andReturn("Test Name");
        $tag->shouldReceive("getTag")->with("roomName")->once()->andReturn("Test Room");
        $device->shouldReceive("getXml")->with("/xml/device_description.xml")->once()->andReturn($parser);

        return $device;
    }

    protected function getSpeaker(DeviceInterface $device)
    {
        $device->shouldReceive("isSpeaker")->once()->andReturn(true);

        return new Speaker($device);
    }

    protected function getController(DeviceInterface $device)
    {
        $speaker = $this->getSpeaker($device);

        $parser = Mockery::mock(XmlParser::class);
        $players = Mockery::mock(XmlParser::class);
        $player = Mockery::mock(XmlParser::class);
        $parser->shouldReceive("getTag")->with("ZonePlayers")->once()->andReturn($players);
        $players->shouldReceive("getTags")->with("ZonePlayer")->once()->andReturn([$player]);
        $player->shouldReceive("getAttributes")->once()->andReturn([
            "location"      =>  "http://192.168.0.66",
            "group"         =>  "",
            "coordinator"   =>  "true",
            "uuid"          =>  "",
        ]);
        $device->shouldReceive("getXml")->with("/status/topology")->once()->andReturn($parser);

        return new Controller($speaker, $this->network);
    }
}
