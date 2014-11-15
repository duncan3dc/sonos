<?php

namespace duncan3dc\Sonos\Test;

use duncan3dc\Sonos\Controller;
use duncan3dc\Sonos\Network;

class ControllerTest extends SonosTest
{

    public function testConstructor1()
    {
        foreach (Network::getSpeakers() as $speaker) {
            if ($speaker->isCoordinator()) {
                $controller = new Controller($speaker);
                $this->assertSame($speaker->ip, $controller->ip);
                return;
            }
        }

        $this->markTestSkipped("No speakers found that are the coordinator of their group");
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructor2()
    {
        foreach (Network::getSpeakers() as $speaker) {
            if (!$speaker->isCoordinator()) {
                $controller = new Controller($speaker);
                return;
            }
        }

        $this->markTestSkipped("No speakers found that are not the coordinator of their group");
    }


    public function testIsCoordinator()
    {
        $this->assertTrue(Network::getController()->isCoordinator());
    }


    public function testGetStateName()
    {
        $states = ["STOPPED", "PAUSED_PLAYBACK", "PLAYING", "TRANSITIONING"];
        foreach (Network::getControllers() as $controller) {
            $this->assertContains($controller->getStateName(), $states);
        }
    }


    public function testGetState()
    {
        $states = [Controller::STATE_STOPPED, Controller::STATE_PLAYING, Controller::STATE_PAUSED, Controller::STATE_TRANSITIONING];
        foreach (Network::getControllers() as $controller) {
            $this->assertContains($controller->getState(), $states);
        }
    }


    public function testGetStateDetails()
    {
        $keys = ["title", "artist", "album", "track-number", "queue-number", "duration", "position", "stream"];
        $state = Network::getController()->getStateDetails();
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $state);
            if (in_array($key, ["track-number", "queue-number"])) {
                $this->assertInternalType("integer", $state[$key]);
            } elseif ($key !== "stream") {
                $this->assertInternalType("string", $state[$key]);
            }
        }
    }


    public function testNext()
    {
        $controller = Network::getController();
        $number = $controller->getStateDetails()["queue-number"];
        $controller->next();
        $this->assertSame($controller->getStateDetails()["queue-number"], $number + 1);
    }


    public function testPrevious()
    {
        $controller = Network::getController();
        $number = $controller->getStateDetails()["queue-number"];
        $controller->previous();
        $this->assertSame($controller->getStateDetails()["queue-number"], $number - 1);
    }


    public function testGetSpeakers()
    {
        $speakers = Network::getController()->getSpeakers();
        $this->assertContainsOnlyInstancesOf("duncan3dc\\Sonos\\Speaker", $speakers);
    }


    public function testSetVolume()
    {
        $controller = Network::getController();
        $volume = 3;
        $controller->setVolume($volume);
        foreach ($controller->getSpeakers() as $speaker) {
            $this->assertSame($volume, $speaker->getVolume());
        }
    }


    public function testAdjustVolume1()
    {
        $controller = Network::getController();
        $volume = 3;
        $controller->setVolume($volume);
        $controller->adjustVolume($volume);
        foreach ($controller->getSpeakers() as $speaker) {
            $this->assertSame($volume * 2, $speaker->getVolume());
        }
    }


    public function testAdjustVolume2()
    {
        $controller = Network::getController();
        $volume = 3;
        $controller->setVolume($volume);
        $controller->adjustVolume($volume * -1);
        foreach ($controller->getSpeakers() as $speaker) {
            $this->assertSame(0, $speaker->getVolume());
        }
    }


    public function testGetMode()
    {
        $mode = Network::getController()->getMode();
        $this->assertInternalType("boolean", $mode["shuffle"]);
        $this->assertInternalType("boolean", $mode["repeat"]);
    }


    public function testSetMode1()
    {
        $controller = Network::getController();

        $controller->setMode([
            "shuffle"   =>  true,
            "repeat"    =>  true,
        ]);

        $mode = $controller->getMode();
        $this->assertTrue($mode["shuffle"]);
        $this->assertTrue($mode["repeat"]);
    }


    public function testSetMode2()
    {
        $controller = Network::getController();

        $controller->setMode([
            "shuffle"   =>  false,
            "repeat"    =>  false,
        ]);

        $mode = $controller->getMode();
        $this->assertFalse($mode["shuffle"]);
        $this->assertFalse($mode["repeat"]);
    }


    public function testGetRepeat()
    {
        $controller = Network::getController();
        $controller->setRepeat(true);
        $this->assertTrue($controller->getRepeat());
    }


    public function testSetRepeat()
    {
        $controller = Network::getController();
        $controller->setRepeat(false);
        $this->assertFalse($controller->getRepeat());
    }


    public function testGetShuffle()
    {
        $controller = Network::getController();
        $controller->setShuffle(true);
        $this->assertTrue($controller->getShuffle());
    }


    public function testSetShuffle()
    {
        $controller = Network::getController();
        $controller->setShuffle(false);
        $this->assertFalse($controller->getShuffle());
    }


    public function testGetQueue()
    {
        $this->assertInstanceOf("duncan3dc\\Sonos\\Queue", Network::getController()->getQueue());
    }
}
