<?php

namespace duncan3dc\SonosTests\Devices;

use duncan3dc\ObjectIntruder\Intruder;
use duncan3dc\Sonos\Devices\Discovery;
use duncan3dc\Sonos\Interfaces\Devices\CollectionInterface;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use duncan3dc\Sonos\Interfaces\Utils\SocketInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DiscoveryTest extends TestCase
{
    /** @var CollectionInterface&MockInterface */
    private $collection;

    /** @var Intruder */
    private $discovery;

    protected function setUp(): void
    {
        $this->collection = Mockery::mock(CollectionInterface::class);
        $this->collection->shouldReceive("getLogger")->with()->andReturn(new NullLogger());

        $discovery = new Discovery($this->collection);
        $this->discovery = new Intruder($discovery);
    }


    protected function tearDown(): void
    {
        Mockery::close();
    }


    public function testSetLogger(): void
    {
        $discovery = new Discovery($this->collection);

        $logger = Mockery::mock(LoggerInterface::class);
        $this->collection->shouldReceive("setLogger")->with($logger)->once();

        $result = $discovery->setLogger($logger);
        $this->assertSame($discovery, $result);
    }


    public function testGetLogger(): void
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $collection = Mockery::mock(CollectionInterface::class);
        $collection->shouldReceive("getLogger")->with()->once()->andReturn($logger);

        $discovery = new Discovery($collection);

        $result = $discovery->getLogger();
        $this->assertSame($logger, $result);
    }


    public function testGetNetworkInterface(): void
    {
        $this->assertNull($this->discovery->getNetworkInterface());
    }


    public function testSetNetworkInterfaceString(): void
    {
        $this->discovery->setNetworkInterface("eth0");
        $this->assertSame("eth0", $this->discovery->getNetworkInterface());
    }


    public function testSetNetworkInterfaceInteger(): void
    {
        $this->discovery->setNetworkInterface(0);
        $this->assertSame(0, $this->discovery->getNetworkInterface());
    }


    public function testSetNetworkInterfaceEmptyString(): void
    {
        $this->discovery->setNetworkInterface("");
        $this->assertSame("", $this->discovery->getNetworkInterface());
    }


    public function testGetMulticastAddress(): void
    {
        $this->assertSame("239.255.255.250", $this->discovery->getMulticastAddress());
    }


    public function testSetMulticastAddress(): void
    {
        $discovery = new Discovery($this->collection);

        $this->assertSame($discovery, $discovery->setMulticastAddress("127.0.0.1"));
        $this->assertSame("127.0.0.1", $discovery->getMulticastAddress());
    }


    public function testAddDevice(): void
    {
        $discovery = new Discovery($this->collection);

        $device = Mockery::mock(DeviceInterface::class);
        $this->collection->shouldReceive("addDevice")->with($device)->once();

        $result = $discovery->addDevice($device);
        $this->assertSame($discovery, $result);
    }


    public function testAddIp(): void
    {
        $discovery = new Discovery($this->collection);

        $this->collection->shouldReceive("addIp")->with("192.168.9.25")->once();

        $result = $discovery->addIp("192.168.9.25");
        $this->assertSame($discovery, $result);
    }


    private function getSocket(string $type): SocketInterface
    {
        $socket = Mockery::mock(SocketInterface::class);

        $socket
            ->shouldReceive("request")
            ->with()
            ->once()
            ->andReturn(file_get_contents(__DIR__ . "/discovery/{$type}.http"));

        return $socket;
    }


    public function testDiscoverDevicesNormal(): void
    {
        $socket = $this->getSocket("normal");

        $this->collection->shouldReceive("addIp")->with("192.168.7.105")->once()->andReturn($this->collection);
        $this->collection->shouldReceive("addIp")->with("192.168.7.103")->once()->andReturn($this->collection);

        $this->discovery->discoverDevices($socket);
        $this->assertTrue(true);
    }


    public function testDiscoverDevicesEmpty(): void
    {
        $socket = $this->getSocket("empty");

        $this->discovery->discoverDevices($socket);
        $this->assertTrue(true);
    }


    public function testDiscoverDevicesLineBreaks(): void
    {
        $socket = $this->getSocket("line-breaks");

        $this->collection->shouldReceive("addIp")->with("192.168.11.5")->once()->andReturn($this->collection);
        $this->collection->shouldReceive("addIp")->with("192.168.11.77")->once()->andReturn($this->collection);

        $this->discovery->discoverDevices($socket);
        $this->assertTrue(true);
    }


    public function testDiscoverDevicesDuplicates(): void
    {
        $socket = $this->getSocket("duplicates");

        $this->collection->shouldReceive("addIp")->with("192.168.7.103")->once()->andReturn($this->collection);

        $this->discovery->discoverDevices($socket);
        $this->assertTrue(true);
    }


    public function testClear(): void
    {
        $discovery = new Discovery($this->collection);

        $this->collection->shouldReceive("clear")->with()->once();

        $result = $discovery->clear();
        $this->assertSame($discovery, $result);
    }
}
