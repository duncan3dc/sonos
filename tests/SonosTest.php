<?php

namespace duncan3dc\Sonos\Test;

use duncan3dc\Sonos\Network;

abstract class SonosTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Network::$cache = true;
        try {
            Network::getSpeakers();
        } catch (\Exception $e) {
            $this->markTestSkipped("No speakers found on the current network");
        }
    }
}
