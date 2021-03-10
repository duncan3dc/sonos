<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\Interfaces\NetworkInterface;
use duncan3dc\Sonos\Network;
use PHPUnit\Framework\TestCase;
use function getenv;

abstract class LiveTest extends TestCase
{
    /** @var NetworkInterface */
    protected $network;

    protected function setUp()
    {
        $this->network = new Network();

        if (!getenv("SONOS_LIVE_TESTS")) {
            $this->markTestSkipped("Ignoring live tests (set the SONOS_LIVE_TESTS environment variable to run)");
            return;
        }

        try {
            $this->network->getSpeakers();
        } catch (\Exception $e) {
            $this->markTestSkipped("No speakers found on the current network");
        }
    }
}
