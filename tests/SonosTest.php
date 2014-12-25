<?php

namespace duncan3dc\Sonos\Test;

use duncan3dc\Sonos\Network;

abstract class SonosTest extends \PHPUnit_Framework_TestCase
{
    protected $network;

    public function setUp()
    {
        $this->network = new Network;

        $this->network->cache = true;
        try {
            $this->network->getSpeakers();
        } catch (\Exception $e) {
            $this->markTestSkipped("No speakers found on the current network");
        }
    }
}
