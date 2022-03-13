<?php

namespace duncan3dc\SonosTests;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Controller;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use duncan3dc\Sonos\Interfaces\NetworkInterface;
use duncan3dc\Sonos\Speaker;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

abstract class MockTest extends TestCase
{
    /** @var NetworkInterface&MockInterface */
    protected $network;

    public function tearDown(): void
    {
        Mockery::close();
    }

    public function setUp(): void
    {
        $this->network = Mockery::mock(NetworkInterface::class);

        $this->network->shouldReceive("getSpeakers")->andReturn([]);
    }

    /**
     * @return DeviceInterface&MockInterface
     */
    protected function getDevice(string $ip = "192.168.0.66"): MockInterface
    {
        $device = Mockery::mock(DeviceInterface::class);
        $device->shouldReceive("getIp")->andReturn($ip);

        $parser = Mockery::mock(XmlParser::class);
        $tag = Mockery::mock(XmlParser::class);
        $parser->shouldReceive("getTag")->with("device")->once()->andReturn($tag);
        $tag->shouldReceive("getTag")->with("friendlyName")->once()->andReturn("Test Name");
        $tag->shouldReceive("getTag")->with("roomName")->once()->andReturn("Test Room");
        $tag->shouldReceive("getTag")->with("UDN")->once()->andReturn("uuid:RINCON_5CAAFD472E1C01400");
        $device->shouldReceive("getXml")->with("/xml/device_description.xml")->once()->andReturn($parser);

        return $device;
    }

    /**
     * @param DeviceInterface&MockInterface $device
     * @return Speaker
     */
    protected function getSpeaker($device)
    {
        $device->shouldReceive("isSpeaker")->once()->andReturn(true);

        $device->shouldReceive("soap")->with("ZoneGroupTopology", "GetZoneGroupAttributes", [])->andReturn([
            "CurrentZoneGroupID" => "RINCON_5CAAFD472E1C01400:916619538",
            "CurrentZonePlayerUUIDsInGroup" => "RINCON_5CAAFD472E1C01400",
        ]);

        return new Speaker($device);
    }

    /**
     * @param DeviceInterface&MockInterface $device
     */
    protected function getController(DeviceInterface $device): Controller
    {
        $speaker = $this->getSpeaker($device);

        return new Controller($speaker, $this->network);
    }
}
