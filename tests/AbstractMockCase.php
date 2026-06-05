<?php

namespace duncan3dc\SonosTests;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Controller;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use duncan3dc\Sonos\Interfaces\NetworkInterface;
use duncan3dc\Sonos\Speaker;
use duncan3dc\Sonos\Utils\SoapResponse;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractMockCase extends TestCase
{
    /** @var NetworkInterface&MockInterface */
    protected $network;

    protected function tearDown(): void
    {
        Mockery::close();
    }

    protected function setUp(): void
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

        $device->shouldReceive("getName")->with()->andReturn("Test Name");
        $device->shouldReceive("getRoom")->with()->andReturn("Test Room");
        $device->shouldReceive("getUuid")->with()->andReturn("RINCON_5CAAFD472E1C01400");
        $device->shouldReceive("getModel")->with()->andReturn("S1");

        return $device;
    }

    /**
     * @param DeviceInterface&MockInterface $device
     * @return Speaker
     */
    protected function getSpeaker($device)
    {
        $device->shouldReceive("soap")->with("ZoneGroupTopology", "GetZoneGroupAttributes", [])->andReturn(new SoapResponse([
            "CurrentZoneGroupID" => "RINCON_5CAAFD472E1C01400:916619538",
            "CurrentZonePlayerUUIDsInGroup" => "RINCON_5CAAFD472E1C01400",
        ]));

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
