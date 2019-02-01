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
    public function tearDown(): void
    {
        Mockery::close();
    }


    public function testCreateFromDefaults(): void
    {
        $factory = new Factory();
        $device = $factory->create("192.168.4.1");

        $this->assertInstanceOf(DeviceInterface::class, $device);
    }


    public function testCreateWithDependencies(): void
    {
        $cache = Mockery::mock(CacheInterface::class);
        $logger = Mockery::mock(LoggerInterface::class);

        $factory = new Factory($cache, $logger);
        $device = $factory->create("192.168.4.1");

        $cache
            ->shouldReceive("has")
            ->with("192.168.4.1_test.xml")
            ->once()
            ->andReturn(true);

        $logger
            ->shouldReceive("info")
            ->with("getting xml from cache: http://192.168.4.1:1400/test.xml")
            ->once()
            ->andReturn(true);

        $cache
            ->shouldReceive("get")
            ->with("192.168.4.1_test.xml")
            ->once()
            ->andReturn("<test>ok</test>");

        $result = $device->getXml("/test.xml");
        $this->assertSame("ok", $result->getTag("test")->nodeValue);
    }
}
