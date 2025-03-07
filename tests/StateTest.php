<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Dom\Xml\Parser;
use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\State;
use duncan3dc\Sonos\Utils\Xml;
use duncan3dc\SonosTests\Tracks\TrackTest;
use Mockery;

class StateTest extends TrackTest
{
    protected function setUp(): void
    {
        $controller = Mockery::mock(ControllerInterface::class);
        $controller->shouldReceive("getIp")->andReturn("192.168.0.66");

        $xml = new Parser($this->xml1);
        $this->track1 = State::createFromXml(Xml::tag($xml, "track"), $controller);

        $xml = new Parser($this->xml2);
        $this->track2 = State::createFromXml(Xml::tag($xml, "track"), $controller);
    }


    /**
     * Ignore these inherited tests as they are Track specific.
     */
    public function testItemId1(): void
    {
        $this->assertTrue(true);
    }
    public function testGetId2(): void
    {
        $this->assertTrue(true);
    }
}
