<?php

namespace duncan3dc\SonosTests\Tracks;

use duncan3dc\Dom\ElementInterface;
use duncan3dc\Dom\Xml\Parser;
use duncan3dc\Dom\Xml\Writer;
use duncan3dc\Sonos\Tracks\Deezer;
use duncan3dc\Sonos\Tracks\Factory;
use duncan3dc\Sonos\Tracks\Spotify;
use duncan3dc\Sonos\Tracks\Stream;
use duncan3dc\Sonos\Tracks\Track;
use duncan3dc\Sonos\Utils\Xml;
use duncan3dc\SonosTests\MockFactory;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    private Factory $factory;

    protected function setUp(): void
    {
        $device = MockFactory::device();
        $controller = MockFactory::controller($device);
        $this->factory = new Factory($controller);
    }


    public function testNetworkTrackUri(): void
    {
        $uri = "x-file-cifs://server/share/song.mp3";
        $track = $this->factory->createFromUri($uri);
        $this->assertInstanceOf(Track::class, $track);
    }


    public function testSpotifyTrackUri(): void
    {
        $uri = "x-sonos-spotify:spotify:track:123sdfd6";
        $track = $this->factory->createFromUri($uri);
        $this->assertInstanceOf(Spotify::class, $track);
    }


    public function testDeezerTrackUri(): void
    {
        $uri = "x-sonos-http:tr:123sdfd6";
        $track = $this->factory->createFromUri($uri);
        $this->assertInstanceOf(Deezer::class, $track);
    }


    public function testStreamTrackUri(): void
    {
        $uri = "x-sonosapi-stream:s200662?sid=254&flags=8224&sn=0";
        $track = $this->factory->createFromUri($uri);
        $this->assertInstanceOf(Stream::class, $track);
    }


    private function getXml(string $uri, string $title = ""): ElementInterface
    {
        $xml = Writer::createXml([
            "track" =>  [
                "res"   =>  $uri,
                "title" =>  $title,
            ],
        ]);

        $parser = new Parser($xml);
        return Xml::tag($parser, "track");
    }


    public function testNetworkTrackXml(): void
    {
        $xml = $this->getXml("x-file-cifs://server/share/song.mp3");
        $track = $this->factory->createFromXml($xml);
        $this->assertInstanceOf(Track::class, $track);
    }


    public function testSpotifyTrackXml(): void
    {
        $xml = $this->getXml("x-sonos-spotify:spotify:track:123sdfd6");
        $track = $this->factory->createFromXml($xml);
        $this->assertInstanceOf(Spotify::class, $track);
    }


    public function testDeezerTrackXml(): void
    {
        $xml = $this->getXml("x-sonos-http:tr:123sdfd6");
        $track = $this->factory->createFromXml($xml);
        $this->assertInstanceOf(Deezer::class, $track);
    }


    public function testStreamTrackXml(): void
    {
        $xml = $this->getXml("x-sonosapi-stream:s200662?sid=254&flags=8224&sn=0");
        $track = $this->factory->createFromXml($xml);
        $this->assertInstanceOf(Stream::class, $track);
    }
}
