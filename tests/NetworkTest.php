<?php

namespace duncan3dc\SonosTests;

use duncan3dc\ObjectIntruder\Intruder;
use duncan3dc\Sonos\Interfaces\Devices\CollectionInterface;
use duncan3dc\Sonos\Interfaces\SpeakerInterface;
use duncan3dc\Sonos\Network;
use Mockery;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class NetworkTest extends TestCase
{
    private $network;
    private $collection;


    public function setUp()
    {
        $this->collection = Mockery::mock(CollectionInterface::class);
        $this->network = new Network($this->collection);
    }


    public function tearDown()
    {
        Mockery::close();
    }


    public function testSetLogger()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $this->collection->shouldReceive("setLogger")->with($logger)->once();
        $this->assertSame($this->network, $this->network->setLogger($logger));
    }


    public function testGetLogger()
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $this->collection->shouldReceive("getLogger")->with()->once()->andReturn($logger);
        $this->assertSame($logger, $this->network->getLogger());
    }


    private function mockSpeakers()
    {
        $speakers = [];
        foreach (range(1, 3) as $id) {
            $speaker = Mockery::mock(SpeakerInterface::class);
            $speaker->shouldReceive("getIp")->andReturn("127.0.0.{$id}");
            $speakers[] = $speaker;
        }

        $network = new Intruder($this->network);
        $network->speakers = $speakers;

        return $speakers;
    }


    public function testGetSpeakers()
    {
        $this->mockSpeakers();
        $speakers = $this->network->getSpeakers();

        $this->assertSame(3, count($speakers));
        $this->assertContainsOnlyInstancesOf(SpeakerInterface::class, $speakers);
    }
}
