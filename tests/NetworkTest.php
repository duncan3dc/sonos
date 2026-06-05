<?php

namespace duncan3dc\SonosTests;

use duncan3dc\ObjectIntruder\Intruder;
use duncan3dc\Sonos\Interfaces\Devices\CollectionInterface;
use duncan3dc\Sonos\Interfaces\SpeakerInterface;
use duncan3dc\Sonos\Network;
use duncan3dc\Sonos\Utils\SoapResponse;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class NetworkTest extends AbstractMockCase
{
    protected Network $network;

    private CollectionInterface&MockInterface $collection;


    protected function setUp(): void
    {
        $this->collection = Mockery::mock(CollectionInterface::class);
        $this->network = new Network($this->collection);
    }


    protected function tearDown(): void
    {
        Mockery::close();
    }


    #[DoesNotPerformAssertions]
    public function testSetLogger(): void
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $this->collection->shouldReceive("setLogger")->with($logger)->once();
        $this->network->setLogger($logger);
    }


    public function testGetLogger(): void
    {
        $logger = Mockery::mock(LoggerInterface::class);
        $this->collection->shouldReceive("getLogger")->with()->once()->andReturn($logger);
        $this->assertSame($logger, $this->network->getLogger());
    }


    /**
     * @return array<SpeakerInterface&MockInterface>
     */
    private function mockSpeakers(): array
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


    public function testGetSpeakers(): void
    {
        $this->mockSpeakers();
        $speakers = $this->network->getSpeakers();

        $this->assertSame(3, count($speakers));
        $this->assertContainsOnlyInstancesOf(SpeakerInterface::class, $speakers);
    }


    public function testExcludePairedSpeakers(): void
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
                ->andReturn(new SoapResponse([
                    "CurrentZoneGroupID" => $group,
                    "CurrentZonePlayerUUIDsInGroup" => "",
                ]));

            $devices[] = $device;
        }

        $this->collection->shouldReceive("getDevices")->with()->andReturn($devices);

        $this->collection->shouldReceive("getLogger")->with()->andReturn(new NullLogger());
        $speakers = $this->network->getSpeakers();

        $this->assertSame(2, count($speakers));
    }


    public function testCreateAlarm1(): void
    {
        $speaker = Mockery::mock(SpeakerInterface::class);
        $speaker->shouldReceive("getUuid")->once()->with()->andReturn("RINCON_3CBFDE542C8101400");
        $speaker->shouldReceive("soap")->once()->with("AlarmClock", "CreateAlarm", [
            "StartLocalTime" => "00:00:00",
            "Duration" => "00:10:00",
            "Recurrence" => "ONCE",
            "Enabled" => "0",
            "RoomUUID" => "RINCON_3CBFDE542C8101400",
            "ProgramURI" => "x-rincon-buzzer:0",
            "ProgramMetaData" => "",
            "PlayMode" => "NORMAL",
            "Volume" => 10,
            "IncludeLinkedZones" => "0",
        ])->andReturn(new SoapResponse("741"));

        $network = new Intruder($this->network);
        $network->speakers = [$speaker];

        $speaker->shouldReceive("isCoordinator")->with()->andReturn(true);
        $speaker->shouldReceive("getIp")->with()->andReturn("127.0.0.3");
        $speaker->shouldReceive("soap")->with("AlarmClock", "ListAlarms", [])->andReturn(new SoapResponse([
            "CurrentAlarmList" => "<Alarm ID='741'></Alarm>",
        ]));

        $alarm = $this->network->createAlarm($speaker);
        self::assertSame(741, $alarm->getId());
    }
}
