<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\Interfaces\SpeakerInterface;
use duncan3dc\Sonos\Speaker;

use function assert;
use function reset;

class SpeakerLiveTest extends LiveTest
{
    /** @var SpeakerInterface */
    protected $speaker;

    protected function setUp(): void
    {
        parent::setUp();
        $speakers = $this->network->getSpeakers();
        $speaker = reset($speakers);
        assert($speaker instanceof Speaker);
        $this->speaker = $speaker;
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
