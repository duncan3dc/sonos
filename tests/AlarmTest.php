<?php

namespace duncan3dc\Sonos\Test;

use duncan3dc\Sonos\Alarm;
use Mockery;

class AlarmTest extends \PHPUnit_Framework_TestCase
{

    protected function getMockRecurrence($recurrence)
    {
        $xml = Mockery::mock("duncan3dc\DomParser\XmlElement");
        $xml->shouldReceive("getAttribute")->once()->with("ID")->andReturn(-1);
        $xml->shouldReceive("getAttributes")->once()->andReturn([
            "Recurrence"    =>  $recurrence,
        ]);
        return new Alarm($xml);
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
        $this->assertFalse($alarm->getFrequency() === Alarm::EVERYDAY);
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
        $this->assertFalse($alarm->getFrequency() === Alarm::EVERYDAY);
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
        $this->assertFalse($alarm->getFrequency() === Alarm::EVERYDAY);
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
            $this->assertFalse($alarm->getFrequency() === Alarm::EVERYDAY);
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
            $this->assertFalse($alarm->getFrequency() === Alarm::EVERYDAY);
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
        $this->assertFalse($alarm->getFrequency() === Alarm::EVERYDAY);
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
            $this->assertSame(Alarm::EVERYDAY, $alarm->getFrequency());
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
        $this->assertFalse($alarm->everyday());
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
        $this->assertFalse($alarm->everyday());
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
        $this->assertFalse($alarm->everyday());
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
            $this->assertFalse($alarm->everyday());
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
            $this->assertFalse($alarm->everyday());
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
        $this->assertFalse($alarm->everyday());
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
            $this->assertTrue($alarm->everyday());
        }
    }
}
