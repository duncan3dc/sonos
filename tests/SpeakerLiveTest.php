<?php

namespace duncan3dc\SonosTests;

class SpeakerLiveTest extends LiveTest
{
    protected $speaker;

    public function setUp()
    {
        parent::setUp();
        $speakers = $this->network->getSpeakers();
        $this->speaker = reset($speakers);
    }

    public function testMute()
    {
        $this->speaker->mute();
        $this->assertSame(true, $this->speaker->isMuted());
    }

    public function testUnmute()
    {
        $this->speaker->unmute();
        $this->assertSame(false, $this->speaker->isMuted());
    }
}
