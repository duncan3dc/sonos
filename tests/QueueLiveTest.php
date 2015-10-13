<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\Tracks\Track;

class QueueLiveTest extends LiveTest
{
    protected $controller;
    protected $queue;
    protected $state;

    public function setUp()
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


    public function tearDown()
    {
        if ($this->controller) {
            $this->controller->restoreState($this->state);
        }
    }


    public function testAddTrack()
    {
        $uri = "x-file-cifs://TEST/music/artist/album/01-Song.mp3";
        $this->queue->addTrack($uri);

        $tracks = $this->queue->getTracks();

        $this->assertSame(1, count($tracks));

        $track = $tracks[0];
        $this->assertInstanceOf(Track::class, $track);
        $this->assertSame($uri, $track->uri);
    }


    public function testAddTracks()
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
            $this->assertSame($uris[$key], $track->uri);
        }
    }


    public function testRemoveTracks()
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
        $this->assertSame($uris[1], $track->uri);
    }


    public function testClear()
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
