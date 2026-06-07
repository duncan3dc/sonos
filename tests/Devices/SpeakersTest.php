<?php

namespace duncan3dc\SonosTests\Devices;

use duncan3dc\Sonos\Devices\Collection;
use duncan3dc\Sonos\Devices\Speakers;
use duncan3dc\Sonos\Exceptions\InvalidArgumentException;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use duncan3dc\Sonos\Interfaces\Devices\FactoryInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class SpeakersTest extends TestCase
{
    private Speakers $speakers;


    public function setUp(): void
    {
        $this->speakers = new Speakers(new Collection());
    }


    public function testSetLogger1(): void
    {
        $logger = new NullLogger();
        $this->speakers->setLogger($logger);
        $result = $this->speakers->getLogger();
        self::assertSame($logger, $result);
    }


    public function testGetLogger1(): void
    {
        $result = $this->speakers->getLogger();
        self::assertInstanceOf(NullLogger::class, $result);
    }


    /**
     * @return iterable<array<mixed>>
     */
    public static function modelProvider(): iterable
    {
        $models = [
            "S1",
            "S12",
            "S3",
            "S5",
            "S6",
            "S9",
            "S11",
            "S13",
            "S14",
            "S15",
            "S16",
            "S17",
            "S18",
            "S19",
            "S20",
            "S21",
            "S22",
            "S23",
            "S24",
            "S27",
            "S29",
            "S31",
            "S33",
            "S35",
            "S38",
            "ZP80",
            "ZP90",
            "ZP100",
            "ZP120",
        ];
        foreach ($models as $model) {
            yield [$model, true];
        }

        $others = [
            "ZB100",
            "",
            0,
            false,
        ];
        foreach ($others as $model) {
            yield [$model, false];
        }
    }
    #[DataProvider("modelProvider")]
    public function testAddDevice1(mixed $model, bool $expected): void
    {
        $device = \Mockery::mock(DeviceInterface::class);
        $device->shouldReceive("getModel")->atLeast()->once()->with()->andReturn($model);

        if (!$expected) {
            /** @var string $model */
            $this->expectException(InvalidArgumentException::class);
            $this->expectExceptionMessage("This device is not recognised as a speaker model: {$model}");
        }

        $result = $this->speakers->addDevice($device);
        self::assertSame($this->speakers, $result);
    }


    public function testAddIp1(): void
    {
        $factory = \Mockery::mock(FactoryInterface::class);
        $speakers = new Speakers(new Collection($factory));

        $input = [
            "192.168.0.1" => "S1",
            "192.168.0.3" => "S3",
        ];
        foreach ($input as $ip => $model) {
            $device = \Mockery::mock(DeviceInterface::class);
            $device->shouldReceive("getIp")->atLeast()->with()->andReturn($ip);
            $device->shouldReceive("getModel")->atLeast()->with()->andReturn($model);
            $factory->shouldReceive("create")->once()->with($ip)->andReturn($device);
        }

        $speakers->addIp("192.168.0.1");
        $speakers->addIp("192.168.0.3");

        $result = [];
        foreach ($speakers->getDevices() as $device) {
            $result[] = $device->getIp();
        }
        self::assertSame(["192.168.0.1", "192.168.0.3"], $result);
    }


    public function testAddIp2(): void
    {
        $factory = \Mockery::mock(FactoryInterface::class);
        $speakers = new Speakers(new Collection($factory));

        $device = \Mockery::mock(DeviceInterface::class);
        $device->shouldReceive("getIp")->atLeast()->with()->andReturn("192.168.0.2");
        $device->shouldReceive("getModel")->atLeast()->with()->andReturn("S2");
        $factory->shouldReceive("create")->once()->with("192.168.0.2")->andReturn($device);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("This device is not recognised as a speaker model: S2");
        $speakers->addIp("192.168.0.2");
    }


    public function testGetDevices1(): void
    {
        $factory = \Mockery::mock(FactoryInterface::class);
        $collection = new Collection($factory);

        $input = [
            "192.168.0.1" => "S1",
            "192.168.0.2" => "S2",
            "192.168.0.3" => "S3",
        ];
        foreach ($input as $ip => $model) {
            $device = \Mockery::mock(DeviceInterface::class);
            $device->shouldReceive("getIp")->atLeast()->with()->andReturn($ip);
            $device->shouldReceive("getModel")->atLeast()->with()->andReturn($model);
            $collection->addDevice($device);
        }

        $result = [];
        $speakers = new Speakers($collection);
        foreach ($speakers->getDevices() as $device) {
            $result[] = $device->getIp();
        }
        self::assertSame(["192.168.0.1", "192.168.0.3"], $result);
    }


    public function testClear1(): void
    {
        $factory = \Mockery::mock(FactoryInterface::class);
        $speakers = new Speakers(new Collection($factory));

        $input = [
            "192.168.0.1" => "S1",
            "192.168.0.3" => "S3",
        ];
        foreach ($input as $ip => $model) {
            $device = \Mockery::mock(DeviceInterface::class);
            $device->shouldReceive("getIp")->atLeast()->with()->andReturn($ip);
            $device->shouldReceive("getModel")->atLeast()->with()->andReturn($model);
            $speakers->addDevice($device);
        }
        self::assertCount(2, $speakers->getDevices());

        $speakers->clear();
        self::assertCount(0, $speakers->getDevices());
    }
}
