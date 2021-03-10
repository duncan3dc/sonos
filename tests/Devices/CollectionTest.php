<?php

namespace duncan3dc\SonosTests\Devices;

use duncan3dc\Sonos\Devices\Collection;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use duncan3dc\Sonos\Interfaces\Devices\FactoryInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    private $factory;
    private $collection;

    protected function setUp()
    {
        $this->factory = Mockery::mock(FactoryInterface::class);
        $this->collection = new Collection($this->factory);
    }


    protected function tearDown()
    {
        Mockery::close();
    }


    public function testAddDevice()
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


    public function testAddIp()
    {
        $device = Mockery::mock(DeviceInterface::class);
        $this->factory->shouldReceive("create")->with("192.168.2.4")->once()->andReturn($device);

        $result = $this->collection->addIp("192.168.2.4");
        $this->assertSame($this->collection, $result);

        $this->assertSame([$device], $this->collection->getDevices());
    }


    public function testClear()
    {
        $this->testAddDevice();
        $this->assertGreaterThan(0, count($this->collection->getDevices()));

        $this->collection->clear();

        $this->assertSame([], $this->collection->getDevices());
    }
}
