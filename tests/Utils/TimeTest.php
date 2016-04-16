<?php

namespace duncan3dc\SonosTests\Utils;

use duncan3dc\Sonos\Utils\Time;
use PHPUnit\Framework\TestCase;

class TimeTest extends TestCase
{

    public function testParse1()
    {
        $time = Time::parse(55);
        $this->assertSame(55, $time->asInt());
        $this->assertSame("00:00:55", $time->asString());
    }
    public function testParse2()
    {
        $time = Time::parse(":55");
        $this->assertSame(55, $time->asInt());
        $this->assertSame("00:00:55", $time->asString());
    }
    public function testParse3()
    {
        $time = Time::parse("1:55");
        $this->assertSame(115, $time->asInt());
        $this->assertSame("00:01:55", $time->asString());
    }
    public function testParse4()
    {
        $time = Time::parse("01:00");
        $this->assertSame(60, $time->asInt());
        $this->assertSame("00:01:00", $time->asString());
    }
    public function testParse5()
    {
        $time = Time::parse("1:01:01");
        $this->assertSame(3661, $time->asInt());
        $this->assertSame("01:01:01", $time->asString());
    }


    public function testInSeconds1()
    {
        $time = Time::inSeconds(0);
        $this->assertSame(0, $time->asInt());
        $this->assertSame("00:00:00", $time->asString());
    }
    public function testInSeconds2()
    {
        $time = Time::inSeconds(60);
        $this->assertSame(60, $time->asInt());
        $this->assertSame("00:01:00", $time->asString());
    }
    public function testInSeconds3()
    {
        $time = Time::inSeconds(127);
        $this->assertSame(127, $time->asInt());
        $this->assertSame("00:02:07", $time->asString());
    }
    public function testInSeconds4()
    {
        $time = Time::inSeconds(3600);
        $this->assertSame(3600, $time->asInt());
        $this->assertSame("01:00:00", $time->asString());
    }
    public function testInSeconds5()
    {
        $time = Time::inSeconds(3725);
        $this->assertSame(3725, $time->asInt());
        $this->assertSame("01:02:05", $time->asString());
    }


    public function testStart()
    {
        $time = Time::start();
        $this->assertSame(0, $time->asInt());
        $this->assertSame("00:00:00", $time->asString());
    }


    public function testFormat1()
    {
        $time = Time::parse("1:9:4");
        $this->assertSame("01/09/04", $time->format("%H/%M/%S"));
    }
    public function testFormat2()
    {
        $time = Time::parse("01:05:02");
        $this->assertSame("1-5-2", $time->format("%h-%m-%s"));
    }
    public function testFormat3()
    {
        $time = Time::parse("99:59:59");
        $this->assertSame("99-59-59", $time->format("%h-%m-%s"));
    }
    public function testFormat4()
    {
        $time = Time::parse("00:00:00");
        $this->assertSame("0-0-0", $time->format("%h-%m-%s"));
    }


    public function testToString()
    {
        $time = Time::parse("01:02:03");
        $this->assertSame("01:02:03", (string) $time);
    }
}
