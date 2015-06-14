<?php

namespace duncan3dc\Sonos\Test;

use duncan3dc\DomParser\XmlElement;
use duncan3dc\Sonos\Alarm;
use duncan3dc\Sonos\Network;
use duncan3dc\Sonos\Speaker;
use Mockery;

class AlarmTest extends MockTest
{
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

        $this->speaker = Mockery::mock(Speaker::class);
        $this->speaker->shouldReceive("getUuid")->andReturn($attributes["RoomUUID"]);

        $network = Mockery::mock(Network::class);
        $network->shouldReceive("getSpeakers")->andReturn([$this->speaker]);

        return new Alarm($xml, $network);
    }


    protected function getMockRecurrence($recurrence)
    {
        return $this->getMockAlarm([
            "Recurrence"    =>  $recurrence,
        ]);
    }


    public function testFrequencyConstants1()
    {
        $alarm = $this->getMockRecurrence("ON_1");
        $this->assertSame(Alarm::TUESDAY, $alarm->getFrequency() & Alarm::TUESDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::MONDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::WEDNESDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::THURSDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::FRIDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::SATURDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::SUNDAY);
        $this->assertFalse($alarm->getFrequency() === Alarm::ONCE);
        $this->assertFalse($alarm->getFrequency() === Alarm::DAILY);
    }


    public function testFrequencyConstants2()
    {
        $alarm = $this->getMockRecurrence("ON_01");
        $this->assertSame(Alarm::MONDAY, $alarm->getFrequency() & Alarm::MONDAY);
        $this->assertSame(Alarm::TUESDAY, $alarm->getFrequency() & Alarm::TUESDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::WEDNESDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::THURSDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::FRIDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::SATURDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::SUNDAY);
        $this->assertFalse($alarm->getFrequency() === Alarm::ONCE);
        $this->assertFalse($alarm->getFrequency() === Alarm::DAILY);
    }


    public function testFrequencyConstants3()
    {
        $alarm = $this->getMockRecurrence("ON_246");
        $this->assertSame(0, $alarm->getFrequency() & Alarm::MONDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::TUESDAY);
        $this->assertSame(Alarm::WEDNESDAY, $alarm->getFrequency() & Alarm::WEDNESDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::THURSDAY);
        $this->assertSame(Alarm::FRIDAY, $alarm->getFrequency() & Alarm::FRIDAY);
        $this->assertSame(0, $alarm->getFrequency() & Alarm::SATURDAY);
        $this->assertSame(Alarm::SUNDAY, $alarm->getFrequency() & Alarm::SUNDAY);
        $this->assertFalse($alarm->getFrequency() === Alarm::ONCE);
        $this->assertFalse($alarm->getFrequency() === Alarm::DAILY);
    }


    public function testFrequencyConstants4()
    {
        foreach (["ON_56", "WEEKENDS"] as $recurrence) {
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


    public function testFrequencyConstants5()
    {
        foreach (["ON_01234", "WEEKDAYS"] as $recurrence) {
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


    public function testFrequencyConstants6()
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


    public function testFrequencyConstants7()
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


    public function testFrequencyMethods1()
    {
        $alarm = $this->getMockRecurrence("ON_3");
        $this->assertFalse($alarm->onMonday());
        $this->assertFalse($alarm->onTuesday());
        $this->assertFalse($alarm->onWednesday());
        $this->assertTrue($alarm->onThursday());
        $this->assertFalse($alarm->onFriday());
        $this->assertFalse($alarm->onSaturday());
        $this->assertFalse($alarm->onSunday());
        $this->assertFalse($alarm->once());
        $this->assertFalse($alarm->daily());
    }


    public function testFrequencyMethods2()
    {
        $alarm = $this->getMockRecurrence("ON_01");
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


    public function testFrequencyMethods3()
    {
        $alarm = $this->getMockRecurrence("ON_246");
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


    public function testFrequencyMethods4()
    {
        foreach (["ON_56", "WEEKENDS"] as $recurrence) {
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


    public function testFrequencyMethods5()
    {
        foreach (["ON_01234", "WEEKDAYS"] as $recurrence) {
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


    public function testFrequencyMethods6()
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


    public function testFrequencyMethods7()
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


    public function testGetId()
    {
        $alarm = $this->getMockAlarm();
        $this->assertSame(999, $alarm->getId());
    }


    public function testGetRoom()
    {
        $alarm = $this->getMockAlarm([
            "RoomUUID"  =>  "RINCON_TEST",
        ]);
        $this->assertSame("RINCON_TEST", $alarm->getRoom());
    }


    public function testGetTime()
    {
        $alarm = $this->getMockAlarm([
            "StartTime" =>  "1:2",
        ]);
        $this->assertSame("01:02", $alarm->getTime());
    }


    public function testGetDuration()
    {
        $alarm = $this->getMockAlarm([
            "Duration"  =>  "1:2",
        ]);
        $this->assertSame(62, $alarm->getDuration());
    }


    public function testGetVolume()
    {
        $alarm = $this->getMockAlarm([
            "Volume"    =>  "30",
        ]);
        $this->assertSame(30, $alarm->getVolume());
    }


    public function testSetVolume()
    {
        $alarm = $this->getMockAlarm([
            "Volume"    =>  "30",
        ]);
        $this->speaker->shouldReceive("soap")->once()->with("AlarmClock", "UpdateAlarm", Mockery::subset(["Volume" => 50]));

        $alarm->setVolume(50);
        $this->assertSame(50, $alarm->getVolume());
    }


    public function testGetRepeat()
    {
        $alarm = $this->getMockAlarm([
            "PlayMode"  =>  "REPEAT_ALL",
        ]);
        $this->assertTrue($alarm->getRepeat());
    }


    public function testSetRepeat1()
    {
        $alarm = $this->getMockAlarm([
            "PlayMode"  =>  "NORMAL",
        ]);
        $this->speaker->shouldReceive("soap")->once()->with("AlarmClock", "UpdateAlarm", Mockery::subset(["PlayMode" => "REPEAT_ALL"]));

        $this->assertTrue($alarm->setRepeat(true)->getRepeat());
    }
    public function testSetRepeat2()
    {
        $alarm = $this->getMockAlarm([
            "PlayMode"  =>  "REPEAT_ALL",
        ]);

        $this->assertTrue($alarm->setRepeat(true)->getRepeat());
    }


    public function testGetShuffle()
    {
        $alarm = $this->getMockAlarm([
            "PlayMode"  =>  "SHUFFLE",
        ]);
        $this->assertTrue($alarm->getShuffle());
    }


    public function testSetShuffle1()
    {
        $alarm = $this->getMockAlarm([
            "PlayMode"  =>  "NORMAL",
        ]);
        $this->speaker->shouldReceive("soap")->once()->with("AlarmClock", "UpdateAlarm", Mockery::subset(["PlayMode" => "SHUFFLE_NOREPEAT"]));

        $this->assertTrue($alarm->setShuffle(true)->getShuffle());
    }
    public function testSetShuffle2()
    {
        $alarm = $this->getMockAlarm([
            "PlayMode"  =>  "SHUFFLE",
        ]);

        $this->assertTrue($alarm->setShuffle(true)->getShuffle());
    }


    public function testIsActive()
    {
        $alarm = $this->getMockAlarm([
            "Enabled"   =>  "1",
        ]);
        $this->assertTrue($alarm->isActive());
    }


    public function testIsNotActive()
    {
        $alarm = $this->getMockAlarm([
            "Enabled"   =>  "0",
        ]);
        $this->assertFalse($alarm->isActive());
    }


    public function testActivate()
    {
        $alarm = $this->getMockAlarm([
            "Enabled"   =>  "0",
        ]);
        $this->speaker->shouldReceive("soap")->once()->with("AlarmClock", "UpdateAlarm", Mockery::subset(["Enabled" => "1"]));

        $this->assertTrue($alarm->activate()->isActive());
    }


    public function testDeactivate()
    {
        $alarm = $this->getMockAlarm([
            "Enabled"   =>  "1",
        ]);
        $this->speaker->shouldReceive("soap")->once()->with("AlarmClock", "UpdateAlarm", Mockery::subset(["Enabled" => "0"]));

        $this->assertFalse($alarm->deactivate()->isActive());
    }


    public function testDelete()
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
