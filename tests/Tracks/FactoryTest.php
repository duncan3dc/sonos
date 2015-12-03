<?php

namespace duncan3dc\SonosTests\Tracks;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\DomParser\XmlWriter;
use duncan3dc\Sonos\Tracks\Deezer;
use duncan3dc\Sonos\Tracks\Factory;
use duncan3dc\Sonos\Tracks\Google;
use duncan3dc\Sonos\Tracks\GoogleUnlimited;
use duncan3dc\Sonos\Tracks\Spotify;
use duncan3dc\Sonos\Tracks\Stream;
use duncan3dc\Sonos\Tracks\Track;
use duncan3dc\SonosTests\MockTest;

class FactoryTest extends MockTest
{
    protected $factory;

    public function setUp()
    {
        parent::setUp();

        $device = $this->getDevice();
        $controller = $this->getController($device);
        $this->factory = new Factory($controller);
    }


    public function testNetworkTrackUri()
    {
        $track = $this->factory->createFromUri("x-file-cifs://server/share/song.mp3");
        $this->assertInstanceOf(Track::class, $track);
    }


    public function testSpotifyTrackUri()
    {
        $track = $this->factory->createFromUri("x-sonos-spotify:spotify:track:123sdfd6");
        $this->assertInstanceOf(Spotify::class, $track);
    }


    public function testDeezerTrackUri()
    {
        $track = $this->factory->createFromUri("x-sonos-http:tr:123sdfd6");
        $this->assertInstanceOf(Deezer::class, $track);
    }


    public function testGoogleTrackUri()
    {
        $track = $this->factory->createFromUri("x-sonos-http:_dklxfo-EJN34xu9HkcfzBMGUd86HezVdklbzxKIUjyXkqC23MIzxiZu8-PtSkgc.mp3");
        $this->assertInstanceOf(Google::class, $track);
    }


    public function testGoogleUnlimitedTrackUri()
    {
        $track = $this->factory->createFromUri("x-sonos-http:A0DvPDnowsEJN34xu9HkcfzBMGUd86HezVdklbzxKIUjyXkqC23MIzxiZu8-PtSkgc.mp3");
        $this->assertInstanceOf(GoogleUnlimited::class, $track);
    }


    public function testStreamTrackUri()
    {
        $track = $this->factory->createFromUri("x-sonosapi-stream:s200662?sid=254&flags=8224&sn=0");
        $this->assertInstanceOf(Stream::class, $track);
    }


    private function getXml(string $uri, string $title = "")
    {
        $xml = XmlWriter::createXml([
            "track" =>  [
                "res"   =>  $uri,
                "title" =>  $title,
            ],
        ]);

        $parser = new XmlParser($xml);
        return $parser->getTag("track");
    }


    public function testNetworkTrackXml()
    {
        $xml = $this->getXml("x-file-cifs://server/share/song.mp3");
        $track = $this->factory->createFromXml($xml);
        $this->assertInstanceOf(Track::class, $track);
    }


    public function testSpotifyTrackXml()
    {
        $xml = $this->getXml("x-sonos-spotify:spotify:track:123sdfd6");
        $track = $this->factory->createFromXml($xml);
        $this->assertInstanceOf(Spotify::class, $track);
    }


    public function testDeezerTrackXml()
    {
        $xml = $this->getXml("x-sonos-http:tr:123sdfd6");
        $track = $this->factory->createFromXml($xml);
        $this->assertInstanceOf(Deezer::class, $track);
    }


    public function testStreamTrackXml()
    {
        $xml = $this->getXml("x-sonosapi-stream:s200662?sid=254&flags=8224&sn=0");
        $track = $this->factory->createFromXml($xml);
        $this->assertInstanceOf(Stream::class, $track);
    }
}
