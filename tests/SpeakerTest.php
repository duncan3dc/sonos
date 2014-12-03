<?php

namespace duncan3dc\Sonos\Test;

use duncan3dc\Sonos\Speaker;
use Mockery;

class SpeakerTest extends MockTest
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testGetVolume()
    {
        $device = $this->getDevice();
        $speaker = $this->getSpeaker($device);

        $device->shouldReceive("soap")->once()->with("RenderingControl", "GetVolume", [
            "Channel"   =>  "Master",
        ])->andReturn("3");
        $this->assertSame(3, $speaker->getVolume());
    }
}
