<?php

namespace duncan3dc\SonosTests;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Controller;
use duncan3dc\Sonos\State;
use duncan3dc\SonosTests\Tracks\TrackTest;
use Mockery;

class StateTest extends TrackTest
{
    public function setUp()
    {
        $controller = Mockery::mock(Controller::class);
        $controller->shouldReceive("getIp")->andReturn("192.168.0.66");

        $xml = new XmlParser($this->xml1);
        $this->track1 = State::createFromXml($xml->getTag("track"), $controller);

        $xml = new XmlParser($this->xml2);
        $this->track2 = State::createFromXml($xml->getTag("track"), $controller);
    }


    /**
     * Ignore these inherited tests as they are Track specific.
     */
    public function testItemId1()
    {
        $this->assertTrue(true);
    }
    public function testGetId2()
    {
        $this->assertTrue(true);
    }
}
