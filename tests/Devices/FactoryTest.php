<?php

namespace duncan3dc\SonosTests\Devices;

use duncan3dc\Sonos\Devices\Factory;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class FactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }


    public function testCreateFromDefaults(): void
    {
        $factory = new Factory();
        $device = $factory->create("192.168.4.1");

        self::assertSame("192.168.4.1", $device->getIp());
    }


    public function testCreateWithDependencies(): void
    {
        $cache = Mockery::mock(CacheInterface::class);
        $logger = Mockery::mock(LoggerInterface::class);

        $factory = new Factory($cache, $logger);
        $device = $factory->create("192.168.4.1");

        $xml = "<device><friendlyName>Disco</friendlyName><modelNumber>S12</modelNumber></device>";

        $cache->shouldReceive("has")->with("get_xml_192.168.4.1")->once()->andReturn(true);
        $logger->shouldReceive("info")->with("getting xml from cache: http://192.168.4.1:1400/xml/device_description.xml")->once();
        $cache->shouldReceive("get")->with("get_xml_192.168.4.1")->once()->andReturn($xml);
        $logger->shouldReceive("debug")->with("192.168.4.1 model: S12")->once();

        $result = $device->getName();
        $this->assertSame("Disco", $result);
    }
}
