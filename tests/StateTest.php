<?php

namespace duncan3dc\Sonos\Test;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\State;
use duncan3dc\Sonos\Test\Tracks\TrackTest;
use Mockery;

class StateTest extends TrackTest
{
    public function setUp()
    {
        $controller = Mockery::mock("duncan3dc\Sonos\Controller");

        $this->track1 = State::createFromXml(new XmlParser($this->xml1), $controller);
        $this->track2 = State::createFromXml(new XmlParser($this->xml2), $controller);
    }


    public function testTrackNumber()
    {
        $this->assertSame(3, $this->track1->trackNumber);
    }
}
