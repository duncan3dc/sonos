<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Dom\Xml\ElementInterface;
use duncan3dc\Sonos\Controller;
use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use duncan3dc\Sonos\Interfaces\NetworkInterface;
use duncan3dc\Sonos\Speaker;
use duncan3dc\Sonos\Utils\SoapResponse;
use Mockery;
use Mockery\MockInterface;

final class MockFactory
{
    public static function device(string $ip = "192.168.0.66"): DeviceInterface&MockInterface
    {
        $device = Mockery::mock(DeviceInterface::class);
        $device->shouldReceive("getIp")->with()->andReturn($ip);
        $device->shouldReceive("getName")->with()->andReturn("Test Name");
        $device->shouldReceive("getRoom")->with()->andReturn("Test Room");
        $device->shouldReceive("getModel")->with()->andReturn("S1");
        $device->shouldReceive("getUuid")->with()->andReturn("RINCON_5CAAFD472E1C01400");
        return $device;
    }

    public static function controller(DeviceInterface&MockInterface $device): ControllerInterface
    {
        $device->shouldReceive("soap")->with("ZoneGroupTopology", "GetZoneGroupAttributes", [])->andReturn(new SoapResponse([
            "CurrentZoneGroupID" => "RINCON_5CAAFD472E1C01400:916619538",
            "CurrentZonePlayerUUIDsInGroup" => "RINCON_5CAAFD472E1C01400",
        ]));

        $speaker = new Speaker($device);

        $network = Mockery::mock(NetworkInterface::class);
        $network->shouldReceive("getSpeakers")->andReturn([]);

        return new Controller($speaker, $network);
    }

    public static function xmlTag(string $value): ElementInterface
    {
        $element = Mockery::mock(ElementInterface::class);
        $element->shouldReceive("__toString")->zeroOrMoreTimes()->andReturn($value);
        $element->shouldReceive("getValue")->zeroOrMoreTimes()->andReturn($value);
        return $element;
    }
}
