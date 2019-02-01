<?php

namespace duncan3dc\SonosTests;

class SpeakerLiveTest extends LiveTest
{
    protected $speaker;

    public function setUp(): void
    {
        parent::setUp();
        $speakers = $this->network->getSpeakers();
        $this->speaker = reset($speakers);
    }

    public function testMute(): void
    {
        $this->speaker->mute();
        $this->assertSame(true, $this->speaker->isMuted());
    }

    public function testUnmute(): void
    {
        $this->speaker->unmute();
        $this->assertSame(false, $this->speaker->isMuted());
    }
}
