<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\Tracks\Track;

class PlaylistLiveTest extends LiveTest
{
    protected $playlist;
    protected $playlistName = "phpunit-test";

    public function setUp()
    {
        parent::setUp();

        if ($this->network->hasPlaylist($this->playlistName)) {
            $this->playlist = $this->network->getPlaylistByName($this->playlistName);
        } else {
            $this->playlist = $this->network->createPlaylist($this->playlistName);
        }
    }


    public function tearDown()
    {
        if ($this->playlist) {
            $this->playlist->delete();
        }
    }


    public function testGetName()
    {
        $this->assertSame($this->playlistName, $this->playlist->getName());
    }


    public function testAddTrack()
    {
        $uri = "x-file-cifs://TEST/music/artist/album/01-Song.mp3";
        $this->playlist->addTrack($uri);

        $tracks = $this->playlist->getTracks();

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
        $this->playlist->addTracks($uris);

        $tracks = $this->playlist->getTracks();

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
        $this->playlist->addTracks($uris);
        $this->playlist->removeTrack(0);

        $tracks = $this->playlist->getTracks();

        $this->assertSame(1, count($tracks));

        $track = $tracks[0];
        $this->assertInstanceOf(Track::class, $track);
        $this->assertSame($uris[1], $track->uri);
    }


    public function testMoveTracks()
    {
        $uris = [
            "x-file-cifs://TEST/music/artist/album/01-Song.mp3",
            "x-file-cifs://TEST/music/artist/album/02-Song.mp3",
        ];
        $this->playlist->addTracks($uris);

        $this->playlist->moveTrack(0, 1);
        $uris = array_reverse($uris);

        $tracks = $this->playlist->getTracks();

        $this->assertSame(2, count($tracks));

        $this->assertContainsOnlyInstancesOf(Track::class, $tracks);
        foreach ($tracks as $key => $track) {
            $this->assertSame($uris[$key], $track->uri);
        }
    }


    public function testClear()
    {
        $uris = [
            "x-file-cifs://TEST/music/artist/album/01-Song.mp3",
            "x-file-cifs://TEST/music/artist/album/02-Song.mp3",
        ];
        $this->playlist->addTracks($uris);

        $this->playlist->clear();

        $tracks = $this->playlist->getTracks();

        $this->assertSame(0, count($tracks));
    }
}
