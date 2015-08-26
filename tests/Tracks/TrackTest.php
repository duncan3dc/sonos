<?php

namespace duncan3dc\SonosTests\Tracks;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Tracks\Track;
use Mockery;

class TrackTest extends \PHPUnit_Framework_TestCase
{
    protected $xml1 = <<<XML
            <track>
                <title>TITLE</title>
                <creator>ARTIST</creator>
                <album>ALBUM</album>
                <originalTrackNumber>3</originalTrackNumber>
            </track>
XML;

    protected $xml2 = <<<XML
            <track>
                <title>TITLE</title>
                <creator>ARTIST</creator>
                <album>ALBUM</album>
                <originalTrackNumber>0</originalTrackNumber>
                <streamContent>Tesseract - Of Matter - Proxy</streamContent>
                <albumArtURI>cover.jpg</albumArtURI>
            </track>
XML;

    protected $track1;
    protected $track2;

    public function setUp()
    {
        $controller = Mockery::mock("duncan3dc\Sonos\Controller");

        $this->track1 = Track::createFromXml(new XmlParser($this->xml1), $controller);
        $this->track2 = Track::createFromXml(new XmlParser($this->xml2), $controller);
    }


    public function tearDown()
    {
        Mockery::close();
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


    public function testGetUri()
    {
        $track = new Track("URI");
        $this->assertSame("URI", $track->getUri());
    }


    public function testGetMetaData()
    {
        $xml = '<DIDL-Lite ';
            $xml .= 'xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/" ';
            $xml .= 'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
            $xml .= 'xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/" ';
            $xml .= 'xmlns:r="urn:schemas-rinconnetworks-com:metadata-1-0/">';
            $xml .= '<item id="-1" parentID="-1" restricted="true">';
                $xml .= '<res></res>';
                $xml .= '<upnp:albumArtURI></upnp:albumArtURI>';
                $xml .= '<dc:title>TITLE</dc:title>';
                $xml .= '<upnp:class>object.item.audioItem.musicTrack</upnp:class>';
                $xml .= '<dc:creator>ARTIST</dc:creator>';
                $xml .= '<upnp:album>ALBUM</upnp:album>';
            $xml .= '</item>';
        $xml .= '</DIDL-Lite>';
        $this->assertSame($xml, $this->track1->getMetadata());
    }
}
