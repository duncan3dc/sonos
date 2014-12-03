<?php

namespace duncan3dc\Sonos\Test;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Controller;
use duncan3dc\Sonos\Device;
use duncan3dc\Sonos\Network;
use duncan3dc\Sonos\Speaker;
use Mockery;

abstract class MockTest extends \PHPUnit_Framework_TestCase
{
    protected $network;

    public function tearDown()
    {
        Mockery::close();
    }

    public function setUp()
    {
        $this->network = new Network;
    }

    protected function getDevice()
    {
        $device = Mockery::mock("duncan3dc\Sonos\Device");
        $device->ip = "192.168.0.66";

        $parser = Mockery::mock("duncan3dc\DomParser\XmlParser");
        $tag = Mockery::mock("duncan3dc\DomParser\XmlParser");
        $parser->shouldReceive("getTag")->with("device")->once()->andReturn($tag);
        $tag->shouldReceive("getTag")->with("friendlyName")->once()->andReturn("Test Name");
        $tag->shouldReceive("getTag")->with("roomName")->once()->andReturn("Test Room");
        $device->shouldReceive("getXml")->with("/xml/device_description.xml")->once()->andReturn($parser);

        return $device;
    }

    protected function getSpeaker(Device $device)
    {
        return new Speaker($device);
    }

    protected function getController(Device $device)
    {
        $speaker = $this->getSpeaker($device);

        $parser = Mockery::mock("duncan3dc\DomParser\XmlParser");
        $players = Mockery::mock("duncan3dc\DomParser\XmlParser");
        $player = Mockery::mock("duncan3dc\DomParser\XmlParser");
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
