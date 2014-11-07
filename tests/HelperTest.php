<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;

class HelperTest extends \PHPUnit_Framework_TestCase
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


    public function __construct()
    {
        $this->xml1 = new XmlParser($this->xml1);
        $this->xml2 = new XmlParser($this->xml2);
    }


    public function testGetTrackMetaDataTitle()
    {
        $meta = Helper::getTrackMetaData($this->xml1);
        $this->assertSame("TITLE", $meta["title"]);
    }


    public function testGetTrackMetaDataArtist()
    {
        $meta = Helper::getTrackMetaData($this->xml1);
        $this->assertSame("ARTIST", $meta["artist"]);
    }


    public function testGetTrackMetaDataAlbum()
    {
        $meta = Helper::getTrackMetaData($this->xml1);
        $this->assertSame("ALBUM", $meta["album"]);
    }


    public function testGetTrackMetaDataTrackNumber()
    {
        $meta = Helper::getTrackMetaData($this->xml1);
        $this->assertSame(3, $meta["track-number"]);
    }


    public function testGetTrackMetaDataStreamTitle()
    {
        $meta = Helper::getTrackMetaData($this->xml2);
        $this->assertSame("Of Matter - Proxy", $meta["title"]);
    }


    public function testGetTrackMetaDataStreamArtist()
    {
        $meta = Helper::getTrackMetaData($this->xml2);
        $this->assertSame("Tesseract", $meta["artist"]);
    }


    public function testGetTrackMetaDataStreamAlbum()
    {
        $meta = Helper::getTrackMetaData($this->xml2);
        $this->assertSame("", $meta["album"]);
    }


    public function testGetTrackMetaDataStreamTrackNumber()
    {
        $meta = Helper::getTrackMetaData($this->xml2);
        $this->assertSame(0, $meta["track-number"]);
    }
}
