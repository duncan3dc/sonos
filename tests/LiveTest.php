<?php

namespace duncan3dc\Sonos\Test;

use duncan3dc\Sonos\Network;

abstract class LiveTest extends \PHPUnit_Framework_TestCase
{
    protected $network;

    public function setUp()
    {
        $this->network = new Network;

        if (in_array("--live-tests", $_SERVER["argv"])) {
            try {
                $this->network->getSpeakers();
            } catch (\Exception $e) {
                $this->markTestSkipped("No speakers found on the current network");
            }
        } else {
            $this->markTestSkipped("Ignoring live tests (these can be run using the --live-tests option)");
        }
    }
}
