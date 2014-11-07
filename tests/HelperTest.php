<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;

class HelperTest extends \PHPUnit_Framework_TestCase
{
    private $xml1 = "<track><title>TITLE</title><creator>ARTIST</creator><album>ALBUM</album><originalTrackNumber>3</originalTrackNumber></track>";


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


    public function testGetTrackMetaDataXmlParser()
    {
        $meta = Helper::getTrackMetaData(new XmlParser($this->xml1));
        $this->assertSame(3, $meta["track-number"]);
    }
}
