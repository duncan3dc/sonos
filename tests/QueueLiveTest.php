<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\Interfaces\ControllerStateInterface;
use duncan3dc\Sonos\Interfaces\QueueInterface;
use duncan3dc\Sonos\Tracks\Track;

class QueueLiveTest extends LiveTest
{
    /** @var ControllerInterface|null */
    protected $controller;

    /** @var QueueInterface */
    protected $queue;

    /** @var ControllerStateInterface */
    protected $state;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controller = $this->network->getController();

        # Backup the current state of the controller so we can leave things as we found them
        $this->state = $this->controller->exportState(true);

        $this->queue = $this->controller->getQueue();

        if ($this->queue->count() > 0) {
            $this->queue->clear();
        }
    }


    protected function tearDown(): void
    {
        if ($this->controller) {
            $this->controller->restoreState($this->state);
        }
    }


    public function testAddTrack(): void
    {
        $uri = "x-file-cifs://TEST/music/artist/album/01-Song.mp3";
        $this->queue->addTrack($uri);

        $tracks = $this->queue->getTracks();

        $this->assertSame(1, count($tracks));

        $track = $tracks[0];
        $this->assertInstanceOf(Track::class, $track);
        $this->assertSame($uri, $track->getUri());
    }


    public function testAddTracks(): void
    {
        $uris = [
            "x-file-cifs://TEST/music/artist/album/01-Song.mp3",
            "x-file-cifs://TEST/music/artist/album/02-Song.mp3",
        ];
        $this->queue->addTracks($uris);

        $tracks = $this->queue->getTracks();

        $this->assertSame(2, count($tracks));

        $this->assertContainsOnlyInstancesOf(Track::class, $tracks);
        foreach ($tracks as $key => $track) {
            $this->assertSame($uris[$key], $track->getUri());
        }
    }


    public function testRemoveTracks(): void
    {
        $uris = [
            "x-file-cifs://TEST/music/artist/album/01-Song.mp3",
            "x-file-cifs://TEST/music/artist/album/02-Song.mp3",
        ];
        $this->queue->addTracks($uris);

        $this->queue->removeTrack(0);

        $tracks = $this->queue->getTracks();

        $this->assertSame(1, count($tracks));

        $track = $tracks[0];
        $this->assertInstanceOf(Track::class, $track);
        $this->assertSame($uris[1], $track->getUri());
    }


    public function testClear(): void
    {
        $uris = [
            "x-file-cifs://TEST/music/artist/album/01-Song.mp3",
            "x-file-cifs://TEST/music/artist/album/02-Song.mp3",
        ];
        $this->queue->addTracks($uris);

        $this->queue->clear();

        $tracks = $this->queue->getTracks();

        $this->assertSame(0, count($tracks));
    }
}
