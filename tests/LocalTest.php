<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\Devices\Collection;
use duncan3dc\Sonos\Devices\Device;
use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\Interfaces\NetworkInterface;
use duncan3dc\Sonos\Interfaces\QueueInterface;
use duncan3dc\Sonos\Network;
use duncan3dc\Sonos\Tracks\Track;
use PHPUnit\Framework\TestCase;

class LocalTest extends TestCase
{
    /** @var NetworkInterface */
    private $network;

    /** @var ControllerInterface */
    private $controller;

    /** @var QueueInterface */
    private $queue;


    public function setUp(): void
    {
        $collection = new Collection();
        $collection->addDevice(new Device("sonos-test"));

        $this->network = new Network($collection);
        $this->controller = $this->network->getController();
        $this->queue = $this->controller->getQueue();
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
