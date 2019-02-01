<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\Controller;
use duncan3dc\Sonos\Interfaces\Utils\TimeInterface;
use duncan3dc\Sonos\Queue;
use duncan3dc\Sonos\Speaker;

class ControllerLiveTest extends LiveTest
{
    public function testConstructor1(): void
    {
        foreach ($this->network->getSpeakers() as $speaker) {
            if ($speaker->isCoordinator()) {
                $controller = new Controller($speaker, $this->network);
                $this->assertSame($speaker->getIp(), $controller->getIp());
                return;
            }
        }

        throw new \Exception("No speakers found that are the coordinator of their group");
    }


    public function testConstructor2(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        foreach ($this->network->getSpeakers() as $speaker) {
            if (!$speaker->isCoordinator()) {
                $controller = new Controller($speaker, $this->network);
                return;
            }
        }

        $this->markTestSkipped("No speakers found that are not the coordinator of their group");
    }


    public function testIsCoordinator(): void
    {
        $this->assertTrue($this->network->getController()->isCoordinator());
    }


    public function testGetStateName(): void
    {
        $states = ["STOPPED", "PAUSED_PLAYBACK", "PLAYING", "TRANSITIONING"];
        foreach ($this->network->getControllers() as $controller) {
            $this->assertContains($controller->getStateName(), $states);
        }
    }


    public function testGetState(): void
    {
        $states = [
            Controller::STATE_STOPPED,
            Controller::STATE_PLAYING,
            Controller::STATE_PAUSED,
            Controller::STATE_TRANSITIONING,
        ];

        foreach ($this->network->getControllers() as $controller) {
            $this->assertContains($controller->getState(), $states);
        }
    }


    public function testGetStateDetails(): void
    {
        $methods = ["getTitle", "getArtist", "getAlbum", "getNumber", "getDuration", "getPosition", "getStream"];
        $state = $this->network->getController()->getStateDetails();
        foreach ($methods as $method) {
            $result = $state->$method();
            if ($method === "getNumber") {
                self::assertIsInt($result);
            } elseif (in_array($method, ["getDuration", "getPosition"], true)) {
                $this->assertInstanceOf(TimeInterface::class, $result);
            } elseif ($method !== "getStream") {
                self::assertIsString($result);
            }
        }
    }


    public function testNext(): void
    {
        $controller = $this->network->getController();
        $number = $controller->getStateDetails()->getNumber();
        $controller->next();
        $this->assertSame($controller->getStateDetails()->getNumber(), $number + 1);
    }


    public function testPrevious(): void
    {
        $controller = $this->network->getController();
        $number = $controller->getStateDetails()->getNumber();
        $controller->previous();
        $this->assertSame($controller->getStateDetails()->getNumber(), $number - 1);
    }


    public function testGetSpeakers(): void
    {
        $speakers = $this->network->getController()->getSpeakers();
        $this->assertContainsOnlyInstancesOf(Speaker::class, $speakers);
    }


    public function testSetVolume(): void
    {
        $controller = $this->network->getController();
        $volume = 3;
        $controller->setVolume($volume);
        foreach ($controller->getSpeakers() as $speaker) {
            $this->assertSame($volume, $speaker->getVolume());
        }
    }


    public function testAdjustVolume1(): void
    {
        $controller = $this->network->getController();
        $volume = 3;
        $controller->setVolume($volume);
        $controller->adjustVolume($volume);
        foreach ($controller->getSpeakers() as $speaker) {
            $this->assertSame($volume * 2, $speaker->getVolume());
        }
    }


    public function testAdjustVolume2(): void
    {
        $controller = $this->network->getController();
        $volume = 3;
        $controller->setVolume($volume);
        $controller->adjustVolume($volume * -1);
        foreach ($controller->getSpeakers() as $speaker) {
            $this->assertSame(0, $speaker->getVolume());
        }
    }


    public function testGetMode(): void
    {
        $mode = $this->network->getController()->getMode();
        self::assertIsBool($mode["shuffle"]);
        self::assertIsBool($mode["repeat"]);
    }


    public function testSetMode1(): void
    {
        $controller = $this->network->getController();

        $controller->setMode([
            "shuffle"   =>  true,
            "repeat"    =>  true,
        ]);

        $mode = $controller->getMode();
        $this->assertTrue($mode["shuffle"]);
        $this->assertTrue($mode["repeat"]);
    }


    public function testSetMode2(): void
    {
        $controller = $this->network->getController();

        $controller->setMode([
            "shuffle"   =>  false,
            "repeat"    =>  false,
        ]);

        $mode = $controller->getMode();
        $this->assertFalse($mode["shuffle"]);
        $this->assertFalse($mode["repeat"]);
    }


    public function testGetRepeat(): void
    {
        $controller = $this->network->getController();
        $controller->setRepeat(true);
        $this->assertTrue($controller->getRepeat());
    }


    public function testSetRepeat(): void
    {
        $controller = $this->network->getController();
        $controller->setRepeat(false);
        $this->assertFalse($controller->getRepeat());
    }


    public function testGetShuffle(): void
    {
        $controller = $this->network->getController();
        $controller->setShuffle(true);
        $this->assertTrue($controller->getShuffle());
    }


    public function testSetShuffle(): void
    {
        $controller = $this->network->getController();
        $controller->setShuffle(false);
        $this->assertFalse($controller->getShuffle());
    }


    public function testGetCrossfade(): void
    {
        $controller = $this->network->getController();
        $controller->setCrossfade(true);
        $this->assertTrue($controller->getCrossfade());
    }


    public function testSetCrossfade(): void
    {
        $controller = $this->network->getController();
        $controller->setCrossfade(false);
        $this->assertFalse($controller->getCrossfade());
    }


    public function testGetQueue(): void
    {
        $this->assertInstanceOf(Queue::class, $this->network->getController()->getQueue());
    }
}
