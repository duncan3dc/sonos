<?php

namespace duncan3dc\SonosTests\Devices;

use duncan3dc\Sonos\Devices\CachedCollection;
use duncan3dc\Sonos\Interfaces\Devices\CollectionInterface;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class CachedCollectionTest extends TestCase
{
    private $collection;
    private $cache;
    private $cachedCollection;

    protected function setUp()
    {
        $this->collection = Mockery::mock(CollectionInterface::class);
        $this->cache = Mockery::mock(CacheInterface::class);
        $this->cachedCollection = new CachedCollection($this->collection, $this->cache);
    }


    protected function tearDown()
    {
        Mockery::close();
    }


    public function testGetDevicesFirstTime()
    {
        $this->cache->shouldReceive("has")->with("device-ip-addresses")->once()->andReturn(false);

        $device = Mockery::mock(DeviceInterface::class);
        $device->shouldReceive("getIp")->with()->once()->andReturn("192.168.2.45");
        $this->collection->shouldReceive("getDevices")->with()->twice()->andReturn([$device]);

        $this->cache->shouldReceive("set")->with("device-ip-addresses", ["192.168.2.45"])->once();
        $this->cache->shouldReceive("get")->with("device-ip-addresses")->once()->andReturn(["192.168.2.45"]);

        $this->collection->shouldReceive("addIp")->with("192.168.2.45")->once();

        $devices = $this->cachedCollection->getDevices();

        $this->assertSame([$device], $devices);
    }


    public function testGetDevicesAlreadyRetrieved()
    {
        $this->testGetDevicesFirstTime();

        $device = Mockery::mock(DeviceInterface::class);
        $this->collection->shouldReceive("getDevices")->with()->once()->andReturn([$device]);

        $devices = $this->cachedCollection->getDevices();

        $this->assertSame([$device], $devices);
    }


    public function testGetDevicesFromCache()
    {
        $this->cache->shouldReceive("has")->with("device-ip-addresses")->once()->andReturn(true);

        $this->cache->shouldReceive("get")->with("device-ip-addresses")->once()->andReturn(["192.168.2.45"]);

        $this->collection->shouldReceive("addIp")->with("192.168.2.45")->once();

        $device = Mockery::mock(DeviceInterface::class);
        $this->collection->shouldReceive("getDevices")->with()->once()->andReturn([$device]);

        $devices = $this->cachedCollection->getDevices();

        $this->assertSame([$device], $devices);
    }


    public function testAddDevice()
    {
        $device = Mockery::mock(DeviceInterface::class);

        $this->cache->shouldReceive("delete")->with("device-ip-addresses")->once();

        $this->collection->shouldReceive("addDevice")->with($device)->once();

        $result = $this->cachedCollection->addDevice($device);
        $this->assertSame($this->cachedCollection, $result);
    }


    public function testAddIp()
    {
        $this->cache->shouldReceive("delete")->with("device-ip-addresses")->once();

        $this->collection->shouldReceive("addIp")->with("192.168.8.0")->once();

        $result = $this->cachedCollection->addIp("192.168.8.0");
        $this->assertSame($this->cachedCollection, $result);
    }


    public function testClear()
    {
        $this->collection->shouldReceive("clear")->with()->once();

        $this->cache->shouldReceive("delete")->with("device-ip-addresses")->once();

        $result = $this->cachedCollection->clear();
        $this->assertSame($this->cachedCollection, $result);
    }
}
