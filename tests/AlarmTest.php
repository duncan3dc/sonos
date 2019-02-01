<?php

namespace duncan3dc\SonosTests;

use duncan3dc\DomParser\XmlElement;
use duncan3dc\Sonos\Alarm;
use duncan3dc\Sonos\Interfaces\NetworkInterface;
use duncan3dc\Sonos\Interfaces\SpeakerInterface;
use duncan3dc\Sonos\Uri;
use Mockery;
use Mockery\MockInterface;

class AlarmTest extends MockTest
{
    /** @var SpeakerInterface|MockInterface */
    protected $speaker;

    public function getMockAlarm(array $attributes = [])
    {
        $attributes = array_merge([
            "StartTime"             =>  "09:00:00",
            "Duration"              =>  "00:01:00",
            "Recurrence"            =>  "ON_1",
            "Enabled"               =>  "1",
            "RoomUUID"              =>  "RINCON_TEST",
            "ProgramURI"            =>  "",
            "ProgramMetaData"       =>  "",
            "PlayMode"              =>  "NORMAL",
            "Volume"                =>  5,
            "IncludeLinkedZones"    =>  "1",
        ], $attributes);

        $xml = Mockery::mock(XmlElement::class);
        $xml->shouldReceive("getAttribute")->once()->with("ID")->andReturn(999);
        $xml->shouldReceive("getAttributes")->once()->andReturn($attributes);

        $this->speaker = Mockery::mock(SpeakerInterface::class);
        $this->speaker->shouldReceive("getUuid")->andReturn($attributes["RoomUUID"]);

        $network = Mockery::mock(NetworkInterface::class);
        $network->shouldReceive("getSpeakers")->andReturn([$this->speaker]);

        return new Alarm($xml, $network);
    }


    protected function getMockRecurrence(string $recurrence)
    {
        return $this->getMockAlarm([
            "Recurrence"    =>  $recurrence,
        ]);
    }


    public function testFrequencyConstants1(): void
    {
        $alarm = $this->getMockRecurrence("ON_1");
        $this->assertSame(Alarm::MONDAY, $alarm->getFrequency() & Alarm::MONDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::TUESDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::WEDNESDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::THURSDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::FRIDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::SATURDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::SUNDAY);
        $this->assertFalse($alarm->getFrequency() === Alarm::ONCE);
        $this->assertFalse($alarm->getFrequency() === Alarm::DAILY);
    }


    public function testFrequencyConstants2(): void
    {
        $alarm = $this->getMockRecurrence("ON_01");
        $this->assertSame(Alarm::MONDAY, $alarm->getFrequency() & Alarm::MONDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::TUESDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::WEDNESDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::THURSDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::FRIDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::SATURDAY);
        $this->assertSame(Alarm::SUNDAY, $alarm->getFrequency() & Alarm::SUNDAY);
        $this->assertFalse($alarm->getFrequency() === Alarm::ONCE);
        $this->assertFalse($alarm->getFrequency() === Alarm::DAILY);
    }


    public function testFrequencyConstants3(): void
    {
        $alarm = $this->getMockRecurrence("ON_246");
        $this->assertSame(0, $alarm->getFrequency() & Alarm::MONDAY);
        $this->assertSame(Alarm::TUESDAY, $alarm->getFrequency() & Alarm::TUESDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::WEDNESDAY);
        $this->assertSame(Alarm::THURSDAY, $alarm->getFrequency() & Alarm::THURSDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::FRIDAY);
        $this->assertSame(Alarm::SATURDAY, $alarm->getFrequency() & Alarm::SATURDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::SUNDAY);
        $this->assertFalse($alarm->getFrequency() === Alarm::ONCE);
        $this->assertFalse($alarm->getFrequency() === Alarm::DAILY);
    }


    public function testFrequencyConstants4(): void
    {
        foreach (["ON_06", "WEEKENDS"] as $recurrence) {
            $alarm = $this->getMockRecurrence($recurrence);
            $this->assertSame(0, $alarm->getFrequency() & Alarm::MONDAY);
            $this->assertSame(0, $alarm->getFrequency() & Alarm::TUESDAY);
            $this->assertSame(0, $alarm->getFrequency() & Alarm::WEDNESDAY);
            $this->assertSame(0, $alarm->getFrequency() & Alarm::THURSDAY);
            $this->assertSame(0, $alarm->getFrequency() & Alarm::FRIDAY);
            $this->assertSame(Alarm::SATURDAY, $alarm->getFrequency() & Alarm::SATURDAY);
            $this->assertSame(Alarm::SUNDAY, $alarm->getFrequency() & Alarm::SUNDAY);
            $this->assertFalse($alarm->getFrequency() === Alarm::ONCE);
            $this->assertFalse($alarm->getFrequency() === Alarm::DAILY);
        }
    }


    public function testFrequencyConstants5(): void
    {
        foreach (["ON_12345", "WEEKDAYS"] as $recurrence) {
            $alarm = $this->getMockRecurrence($recurrence);
            $this->assertSame(Alarm::MONDAY, $alarm->getFrequency() & Alarm::MONDAY);
            $this->assertSame(Alarm::TUESDAY, $alarm->getFrequency() & Alarm::TUESDAY);
            $this->assertSame(Alarm::WEDNESDAY, $alarm->getFrequency() & Alarm::WEDNESDAY);
            $this->assertSame(Alarm::THURSDAY, $alarm->getFrequency() & Alarm::THURSDAY);
            $this->assertSame(Alarm::FRIDAY, $alarm->getFrequency() & Alarm::FRIDAY);
            $this->assertSame(0, $alarm->getFrequency() & Alarm::SATURDAY);
            $this->assertSame(0, $alarm->getFrequency() & Alarm::SUNDAY);
            $this->assertFalse($alarm->getFrequency() === Alarm::ONCE);
            $this->assertFalse($alarm->getFrequency() === Alarm::DAILY);
        }
    }


    public function testFrequencyConstants6(): void
    {
        $alarm = $this->getMockRecurrence("ONCE");
        $this->assertSame(0, $alarm->getFrequency() & Alarm::MONDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::TUESDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::WEDNESDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::THURSDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::FRIDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::SATURDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::SUNDAY);
        $this->assertSame(Alarm::ONCE, $alarm->getFrequency());
        $this->assertFalse($alarm->getFrequency() === Alarm::DAILY);
    }


    public function testFrequencyConstants7(): void
    {
        foreach (["ON_0123456", "DAILY"] as $recurrence) {
            $alarm = $this->getMockRecurrence($recurrence);
            $this->assertSame(Alarm::MONDAY, $alarm->getFrequency() & Alarm::MONDAY);
            $this->assertSame(Alarm::TUESDAY, $alarm->getFrequency() & Alarm::TUESDAY);
            $this->assertSame(Alarm::WEDNESDAY, $alarm->getFrequency() & Alarm::WEDNESDAY);
            $this->assertSame(Alarm::THURSDAY, $alarm->getFrequency() & Alarm::THURSDAY);
            $this->assertSame(Alarm::FRIDAY, $alarm->getFrequency() & Alarm::FRIDAY);
            $this->assertSame(Alarm::SATURDAY, $alarm->getFrequency() & Alarm::SATURDAY);
            $this->assertSame(Alarm::SUNDAY, $alarm->getFrequency() & Alarm::SUNDAY);
            $this->assertFalse($alarm->getFrequency() === Alarm::ONCE);
            $this->assertSame(Alarm::DAILY, $alarm->getFrequency());
        }
    }


    public function testFrequencyMethods1(): void
    {
        $alarm = $this->getMockRecurrence("ON_3");
        $this->assertFalse($alarm->onMonday());
        $this->assertFalse($alarm->onTuesday());
        $this->assertTrue($alarm->onWednesday());
        $this->assertFalse($alarm->onThursday());
        $this->assertFalse($alarm->onFriday());
        $this->assertFalse($alarm->onSaturday());
        $this->assertFalse($alarm->onSunday());
        $this->assertFalse($alarm->once());
        $this->assertFalse($alarm->daily());
    }


    public function testFrequencyMethods2(): void
    {
        $alarm = $this->getMockRecurrence("ON_12");
        $this->assertTrue($alarm->onMonday());
        $this->assertTrue($alarm->onTuesday());
        $this->assertFalse($alarm->onWednesday());
        $this->assertFalse($alarm->onThursday());
        $this->assertFalse($alarm->onFriday());
        $this->assertFalse($alarm->onSaturday());
        $this->assertFalse($alarm->onSunday());
        $this->assertFalse($alarm->once());
        $this->assertFalse($alarm->daily());
    }


    public function testFrequencyMethods3(): void
    {
        $alarm = $this->getMockRecurrence("ON_035");
        $this->assertFalse($alarm->onMonday());
        $this->assertFalse($alarm->onTuesday());
        $this->assertTrue($alarm->onWednesday());
        $this->assertFalse($alarm->onThursday());
        $this->assertTrue($alarm->onFriday());
        $this->assertFalse($alarm->onSaturday());
        $this->assertTrue($alarm->onSunday());
        $this->assertFalse($alarm->once());
        $this->assertFalse($alarm->daily());
    }


    public function testFrequencyMethods4(): void
    {
        foreach (["ON_06", "WEEKENDS"] as $recurrence) {
            $alarm = $this->getMockRecurrence($recurrence);
            $this->assertFalse($alarm->onMonday());
            $this->assertFalse($alarm->onTuesday());
            $this->assertFalse($alarm->onWednesday());
            $this->assertFalse($alarm->onThursday());
            $this->assertFalse($alarm->onFriday());
            $this->assertTrue($alarm->onSaturday());
            $this->assertTrue($alarm->onSunday());
            $this->assertFalse($alarm->once());
            $this->assertFalse($alarm->daily());
        }
    }


    public function testFrequencyMethods5(): void
    {
        foreach (["ON_12345", "WEEKDAYS"] as $recurrence) {
            $alarm = $this->getMockRecurrence($recurrence);
            $this->assertTrue($alarm->onMonday());
            $this->assertTrue($alarm->onTuesday());
            $this->assertTrue($alarm->onWednesday());
            $this->assertTrue($alarm->onThursday());
            $this->assertTrue($alarm->onFriday());
            $this->assertFalse($alarm->onSaturday());
            $this->assertFalse($alarm->onSunday());
            $this->assertFalse($alarm->once());
            $this->assertFalse($alarm->daily());
        }
    }


    public function testFrequencyMethods6(): void
    {
        $alarm = $this->getMockRecurrence("ONCE");
        $this->assertFalse($alarm->onMonday());
        $this->assertFalse($alarm->onTuesday());
        $this->assertFalse($alarm->onWednesday());
        $this->assertFalse($alarm->onThursday());
        $this->assertFalse($alarm->onFriday());
        $this->assertFalse($alarm->onSaturday());
        $this->assertFalse($alarm->onSunday());
        $this->assertTrue($alarm->once());
        $this->assertFalse($alarm->daily());
    }


    public function testFrequencyMethods7(): void
    {
        foreach (["ON_0123456", "DAILY"] as $recurrence) {
            $alarm = $this->getMockRecurrence($recurrence);
            $this->assertTrue($alarm->onMonday());
            $this->assertTrue($alarm->onTuesday());
            $this->assertTrue($alarm->onWednesday());
            $this->assertTrue($alarm->onThursday());
            $this->assertTrue($alarm->onFriday());
            $this->assertTrue($alarm->onSaturday());
            $this->assertTrue($alarm->onSunday());
            $this->assertFalse($alarm->once());
            $this->assertTrue($alarm->daily());
        }
    }


    public function testGetId(): void
    {
        $alarm = $this->getMockAlarm();
        $this->assertSame(999, $alarm->getId());
    }


    public function testGetRoom(): void
    {
        $alarm = $this->getMockAlarm([
            "RoomUUID"  =>  "RINCON_TEST",
        ]);
        $this->assertSame("RINCON_TEST", $alarm->getRoom());
    }


    public function testGetTime(): void
    {
        $alarm = $this->getMockAlarm([
            "StartTime" =>  "01:02:03",
        ]);
        $this->assertSame("01:02:03", $alarm->getTime()->asString());
    }


    public function testGetDuration(): void
    {
        $alarm = $this->getMockAlarm([
            "Duration"  =>  "00:01:02",
        ]);
        $this->assertSame(62, $alarm->getDuration()->asInt());
    }


    public function testGetMusic(): void
    {
        $alarm = $this->getMockAlarm([
            "ProgramURI" => "file:///jffs/settings/savedqueues.rsq#411",
            "ProgramMetaData" => "<DIDL-Lite>Playlist 411</DIDL-Lite>",
        ]);
        $music = $alarm->getMusic();
        $this->assertSame("file:///jffs/settings/savedqueues.rsq#411", $music->getUri());
        $this->assertSame("<DIDL-Lite>Playlist 411</DIDL-Lite>", $music->getMetaData());
    }


    public function testSetMusic(): void
    {
        $alarm = $this->getMockAlarm();
        $this->speaker
            ->shouldReceive("soap")
            ->once()
            ->with("AlarmClock", "UpdateAlarm", Mockery::subset([
                "ProgramURI" => "file:///jffs/settings/savedqueues.rsq#34",
                "ProgramMetaData" => "<DIDL-Lite>Playlist 34</DIDL-Lite>",
            ]));

        $uri = new Uri("file:///jffs/settings/savedqueues.rsq#34", "<DIDL-Lite>Playlist 34</DIDL-Lite>");
        $this->assertSame($alarm, $alarm->setMusic($uri));
        $this->assertSame("file:///jffs/settings/savedqueues.rsq#34", $alarm->getMusic()->getUri());
    }


    public function testGetVolume(): void
    {
        $alarm = $this->getMockAlarm([
            "Volume"    =>  "30",
        ]);
        $this->assertSame(30, $alarm->getVolume());
    }


    public function testSetVolume(): void
    {
        $alarm = $this->getMockAlarm([
            "Volume"    =>  "30",
        ]);
        $this->speaker
            ->shouldReceive("soap")
            ->once()
            ->with("AlarmClock", "UpdateAlarm", Mockery::subset(["Volume" => 50]));

        $alarm->setVolume(50);
        $this->assertSame(50, $alarm->getVolume());
    }


    public function testGetRepeat(): void
    {
        $alarm = $this->getMockAlarm([
            "PlayMode"  =>  "REPEAT_ALL",
        ]);
        $this->assertTrue($alarm->getRepeat());
    }


    public function testSetRepeat1(): void
    {
        $alarm = $this->getMockAlarm([
            "PlayMode"  =>  "NORMAL",
        ]);
        $this->speaker
            ->shouldReceive("soap")
            ->once()
            ->with("AlarmClock", "UpdateAlarm", Mockery::subset(["PlayMode" => "REPEAT_ALL"]));

        $this->assertTrue($alarm->setRepeat(true)->getRepeat());
    }
    public function testSetRepeat2(): void
    {
        $alarm = $this->getMockAlarm([
            "PlayMode"  =>  "REPEAT_ALL",
        ]);

        $this->assertTrue($alarm->setRepeat(true)->getRepeat());
    }


    public function testGetShuffle(): void
    {
        $alarm = $this->getMockAlarm([
            "PlayMode"  =>  "SHUFFLE",
        ]);
        $this->assertTrue($alarm->getShuffle());
    }


    public function testSetShuffle1(): void
    {
        $alarm = $this->getMockAlarm([
            "PlayMode"  =>  "NORMAL",
        ]);
        $this->speaker
            ->shouldReceive("soap")
            ->once()
            ->with("AlarmClock", "UpdateAlarm", Mockery::subset(["PlayMode" => "SHUFFLE_NOREPEAT"]));

        $this->assertTrue($alarm->setShuffle(true)->getShuffle());
    }
    public function testSetShuffle2(): void
    {
        $alarm = $this->getMockAlarm([
            "PlayMode"  =>  "SHUFFLE",
        ]);

        $this->assertTrue($alarm->setShuffle(true)->getShuffle());
    }


    public function testIsActive(): void
    {
        $alarm = $this->getMockAlarm([
            "Enabled"   =>  "1",
        ]);
        $this->assertTrue($alarm->isActive());
    }


    public function testIsNotActive(): void
    {
        $alarm = $this->getMockAlarm([
            "Enabled"   =>  "0",
        ]);
        $this->assertFalse($alarm->isActive());
    }


    public function testActivate(): void
    {
        $alarm = $this->getMockAlarm([
            "Enabled"   =>  "0",
        ]);
        $this->speaker
            ->shouldReceive("soap")
            ->once()
            ->with("AlarmClock", "UpdateAlarm", Mockery::subset(["Enabled" => "1"]));

        $this->assertTrue($alarm->activate()->isActive());
    }


    public function testDeactivate(): void
    {
        $alarm = $this->getMockAlarm([
            "Enabled"   =>  "1",
        ]);
        $this->speaker
            ->shouldReceive("soap")
            ->once()
            ->with("AlarmClock", "UpdateAlarm", Mockery::subset(["Enabled" => "0"]));

        $this->assertFalse($alarm->deactivate()->isActive());
    }


    public function testDelete(): void
    {
        $alarm = $this->getMockAlarm([
            "Enabled"   =>  "1",
        ]);
        $this->speaker->shouldReceive("soap")->once()->with("AlarmClock", "DestroyAlarm", [
            "ID"    =>  999,
        ]);

        $this->assertNull($alarm->delete());
    }
}
