<?php

namespace duncan3dc\SonosTests\Tracks;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\ObjectIntruder\Intruder;
use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\Tracks\Track;
use Mockery;
use PHPUnit\Framework\TestCase;

class TrackTest extends TestCase
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
            <track id="O:345">
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

    protected function setUp()
    {
        $controller = Mockery::mock(ControllerInterface::class);
        $controller->shouldReceive("getIp")->with()->andReturn("192.168.0.66");

        $xml = new XmlParser($this->xml1);
        $this->track1 = Track::createFromXml($xml->getTag("track"), $controller);

        $xml = new XmlParser($this->xml2);
        $this->track2 = Track::createFromXml($xml->getTag("track"), $controller);
    }


    protected function tearDown()
    {
        Mockery::close();
    }


    public function testGetTrackMetaDataTitle()
    {
        $value = "TITLE";
        $this->assertSame($value, $this->track1->getTitle());
    }


    public function testGetTrackMetaDataArtist()
    {
        $value = "ARTIST";
        $this->assertSame($value, $this->track1->getArtist());
    }


    public function testGetTrackMetaDataAlbum()
    {
        $value = "ALBUM";
        $this->assertSame($value, $this->track1->getAlbum());
    }


    public function testGetTrackMetaDataNumber()
    {
        $value = 3;
        $this->assertSame($value, $this->track1->getNumber());
    }


    public function testGetTrackMetaDataStreamTitle()
    {
        $value = "Of Matter - Proxy";
        $this->assertSame($value, $this->track2->getTitle());
    }


    public function testGetTrackMetaDataStreamArtist()
    {
        $value = "Tesseract";
        $this->assertSame($value, $this->track2->getArtist());
    }


    public function testGetTrackMetaDataStreamAlbum()
    {
        $value = "";
        $this->assertSame($value, $this->track2->getAlbum());
    }


    public function testGetTrackMetaDataStreamNumber()
    {
        $value = 0;
        $this->assertSame($value, $this->track2->getNumber());
    }


    public function testItemId1()
    {
        $track = new Intruder($this->track1);
        $this->assertSame("-1", $track->itemId);
    }
    public function testGetId2()
    {
        $track = new Intruder($this->track2);
        $this->assertSame("O:345", $track->itemId);
    }


    public function testGetUri()
    {
        $track = new Track("URI");
        $this->assertSame("URI", $track->getUri());
    }


    public function testGetMetaData1()
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
    public function testGetMetaData2()
    {
        $xml = '<DIDL-Lite ';
            $xml .= 'xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/" ';
            $xml .= 'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
            $xml .= 'xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/" ';
            $xml .= 'xmlns:r="urn:schemas-rinconnetworks-com:metadata-1-0/">';
            $xml .= '<item id="O:345" parentID="-1" restricted="true">';
                $xml .= '<res></res>';
                $xml .= '<upnp:albumArtURI>http://192.168.0.66:1400/cover.jpg</upnp:albumArtURI>';
                $xml .= '<dc:title>Of Matter - Proxy</dc:title>';
                $xml .= '<upnp:class>object.item.audioItem.musicTrack</upnp:class>';
                $xml .= '<dc:creator>Tesseract</dc:creator>';
                $xml .= '<upnp:album></upnp:album>';
            $xml .= '</item>';
        $xml .= '</DIDL-Lite>';
        $this->assertSame($xml, $this->track2->getMetadata());
    }
}
