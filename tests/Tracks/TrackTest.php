<?php

namespace duncan3dc\Sonos\Test\Tracks;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Tracks\Track;
use Mockery;

class TrackTest extends \PHPUnit_Framework_TestCase
{
    private $xml1 = <<<XML
            <track>
                <title>TITLE</title>
                <creator>ARTIST</creator>
                <album>ALBUM</album>
                <originalTrackNumber>3</originalTrackNumber>
            </track>
XML;

    private $xml2 = <<<XML
            <track>
                <title>TITLE</title>
                <creator>ARTIST</creator>
                <album>ALBUM</album>
                <originalTrackNumber>0</originalTrackNumber>
                <streamContent>Tesseract - Of Matter - Proxy</streamContent>
            </track>
XML;


    public function setUp()
    {
        $controller = Mockery::mock("duncan3dc\Sonos\Controller");

        $this->track1 = Track::createFromXml(new XmlParser($this->xml1), $controller);
        $this->track2 = Track::createFromXml(new XmlParser($this->xml2), $controller);
    }


    public function testGetTrackMetaDataTitle()
    {
        $value = "TITLE";
        $this->assertSame($value, $this->track1->title);
    }


    public function testGetTrackMetaDataArtist()
    {
        $value = "ARTIST";
        $this->assertSame($value, $this->track1->artist);
    }


    public function testGetTrackMetaDataAlbum()
    {
        $value = "ALBUM";
        $this->assertSame($value, $this->track1->album);
    }


    public function testGetTrackMetaDataNumber()
    {
        $value = 3;
        $this->assertSame($value, $this->track1->number);
    }


    public function testGetTrackMetaDataStreamTitle()
    {
        $value = "Of Matter - Proxy";
        $this->assertSame($value, $this->track2->title);
    }


    public function testGetTrackMetaDataStreamArtist()
    {
        $value = "Tesseract";
        $this->assertSame($value, $this->track2->artist);
    }


    public function testGetTrackMetaDataStreamAlbum()
    {
        $value = "";
        $this->assertSame($value, $this->track2->album);
    }


    public function testGetTrackMetaDataStreamNumber()
    {
        $value = 0;
        $this->assertSame($value, $this->track2->number);
    }
}
