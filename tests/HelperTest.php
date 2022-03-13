<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\Helper;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    public function testGetModeRepeatAll()
    {
        $mode = Helper::getMode("REPEAT_ALL");
        $this->assertTrue($mode["repeat"]);
        $this->assertFalse($mode["shuffle"]);
    }


    public function testGetModeShuffle()
    {
        $mode = Helper::getMode("SHUFFLE");
        $this->assertTrue($mode["repeat"]);
        $this->assertTrue($mode["shuffle"]);
    }


    public function testGetModeShuffleNoRepeat()
    {
        $mode = Helper::getMode("SHUFFLE_NOREPEAT");
        $this->assertFalse($mode["repeat"]);
        $this->assertTrue($mode["shuffle"]);
    }


    public function testGetModeNormal()
    {
        $mode = Helper::getMode("NORMAL");
        $this->assertFalse($mode["repeat"]);
        $this->assertFalse($mode["shuffle"]);
    }
}
