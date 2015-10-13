<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\Network;

abstract class LiveTest extends \PHPUnit_Framework_TestCase
{
    protected $network;

    public function setUp()
    {
        $this->network = new Network;

        if (empty($_ENV["SONOS_LIVE_TESTS"])) {
            $this->markTestSkipped("Ignoring live tests (these can be run setting the SONOS_LIVE_TESTS environment variable)");
            return;
        }

        try {
            $this->network->getSpeakers();
        } catch (\Exception $e) {
            $this->markTestSkipped("No speakers found on the current network");
        }
    }
}
