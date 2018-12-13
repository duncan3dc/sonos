<?php

namespace duncan3dc\SonosTests;

use duncan3dc\ObjectIntruder\Intruder;
use duncan3dc\Sonos\Interfaces\Devices\CollectionInterface;
use duncan3dc\Sonos\Interfaces\SpeakerInterface;
use duncan3dc\Sonos\Network;
use Mockery;
use Mockery\MockInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class NetworkTest extends MockTest
{
    /** @var Network */
    protected $network;

    /** @var CollectionInterface|MockInterface */
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


    public function testExcludePairedSpeakers()
    {
        $devices = [];

        $setup = [
            "192.168.0.1" => "SPEAKER_A:1",
            "192.168.0.2" => "",
            "192.168.0.3" => "SPEAKER_B:1",
        ];
        foreach ($setup as $ip => $group) {
            $device = $this->getDevice($ip);
            $device->shouldReceive("isSpeaker")->with()->andReturn(true);
            $device->shouldReceive("soap")
                ->with("ZoneGroupTopology", "GetZoneGroupAttributes", [])
                ->andReturn([
                    "CurrentZoneGroupID" => $group,
                    "CurrentZonePlayerUUIDsInGroup" => "",
                ]);

            $devices[] = $device;
        }

        $this->collection->shouldReceive("getDevices")->with()->andReturn($devices);

        $this->collection->shouldReceive("getLogger")->with()->andReturn(new NullLogger());
        $speakers = $this->network->getSpeakers();

        $this->assertSame(2, count($speakers));
    }
}
