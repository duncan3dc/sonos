<?php

namespace duncan3dc\SonosTests\Devices;

use duncan3dc\Sonos\Devices\Collection;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use duncan3dc\Sonos\Interfaces\Devices\FactoryInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    /** @var FactoryInterface|MockInterface */
    private $factory;

    /** @var Collection */
    private $collection;

    public function setUp(): void
    {
        $this->factory = Mockery::mock(FactoryInterface::class);
        $this->collection = new Collection($this->factory);
    }


    public function tearDown(): void
    {
        Mockery::close();
    }


    public function testAddDevice(): void
    {
        $device1 = Mockery::mock(DeviceInterface::class);
        $device1->shouldReceive("getIp")->with()->andReturn("192.168.2.4");

        $device2 = Mockery::mock(DeviceInterface::class);
        $device2->shouldReceive("getIp")->with()->andReturn("192.168.2.4");

        $result = $this->collection->addDevice($device1);
        $this->assertSame($this->collection, $result);

        $this->collection->addDevice($device2);
        $this->assertSame([$device2], $this->collection->getDevices());
    }


    public function testAddIp(): void
    {
        $device = Mockery::mock(DeviceInterface::class);
        $this->factory->shouldReceive("create")->with("192.168.2.4")->once()->andReturn($device);

        $result = $this->collection->addIp("192.168.2.4");
        $this->assertSame($this->collection, $result);

        $this->assertSame([$device], $this->collection->getDevices());
    }


    public function testClear(): void
    {
        $this->testAddDevice();
        $this->assertGreaterThan(0, count($this->collection->getDevices()));

        $this->collection->clear();

        $this->assertSame([], $this->collection->getDevices());
    }
}
