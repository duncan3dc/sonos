<?php

namespace duncan3dc\SonosTests\Tracks;

use duncan3dc\DomParser\XmlElement;
use duncan3dc\Sonos\Tracks\Deezer;
use duncan3dc\Sonos\Tracks\Factory;
use duncan3dc\Sonos\Tracks\Spotify;
use duncan3dc\Sonos\Tracks\Stream;
use duncan3dc\Sonos\Tracks\Track;
use duncan3dc\SonosTests\MockTest;
use Mockery;

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


    public function testStreamTrackUri()
    {
        $track = $this->factory->createFromUri("x-sonosapi-stream:s200662?sid=254&flags=8224&sn=0");
        $this->assertInstanceOf(Stream::class, $track);
    }


    protected function getMockXml($uri)
    {
        $xml = Mockery::mock(XmlElement::class);
        $xml->shouldReceive("getTag")->once()->with("res")->andReturn($uri);

        $xml->shouldReceive("getTag")->with(Mockery::any());
        $xml->shouldReceive("hasAttribute")->with(Mockery::any());

        return $xml;
    }


    public function testNetworkTrackXml()
    {
        $xml = $this->getMockXml("x-file-cifs://server/share/song.mp3");
        $track = $this->factory->createFromXml($xml);
        $this->assertInstanceOf(Track::class, $track);
    }


    public function testSpotifyTrackXml()
    {
        $xml = $this->getMockXml("x-sonos-spotify:spotify:track:123sdfd6");
        $track = $this->factory->createFromXml($xml);
        $this->assertInstanceOf(Spotify::class, $track);
    }


    public function testDeezerTrackXml()
    {
        $xml = $this->getMockXml("x-sonos-http:tr:123sdfd6");
        $track = $this->factory->createFromXml($xml);
        $this->assertInstanceOf(Deezer::class, $track);
    }


    public function testStreamTrackXml()
    {
        $xml = $this->getMockXml("x-sonosapi-stream:s200662?sid=254&flags=8224&sn=0");
        $track = $this->factory->createFromXml($xml);
        $this->assertInstanceOf(Stream::class, $track);
    }
}
