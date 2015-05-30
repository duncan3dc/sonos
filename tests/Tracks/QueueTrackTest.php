<?php

namespace duncan3dc\Sonos\Test\Tracks;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Tracks\QueueTrack;
use Mockery;

class QueueTrackTest extends \PHPUnit_Framework_TestCase
{
    protected $xml = <<<XML
            <track id="345">
                <title>TITLE</title>
                <creator>ARTIST</creator>
                <album>ALBUM</album>
                <originalTrackNumber>3</originalTrackNumber>
            </track>
XML;

    protected $track;

    public function setUp()
    {
        $controller = Mockery::mock("duncan3dc\Sonos\Controller");

        $xml = new XmlParser($this->xml);
        $this->track = QueueTrack::createFromXml($xml->getTag("track"), $controller);
    }


    public function tearDown()
    {
        Mockery::close();
    }


    public function testGetTrackMetaDataTitle()
    {
        $value = "TITLE";
        $this->assertSame($value, $this->track->title);
    }


    public function testGetTrackMetaDataArtist()
    {
        $value = "ARTIST";
        $this->assertSame($value, $this->track->artist);
    }


    public function testGetTrackMetaDataAlbum()
    {
        $value = "ALBUM";
        $this->assertSame($value, $this->track->album);
    }


    public function testGetTrackMetaDataNumber()
    {
        $value = 3;
        $this->assertSame($value, $this->track->number);
    }


    public function testGetId()
    {
        $this->assertSame(345, $this->track->queueId);

        $reflected = new \ReflectionClass($this->track);
        $method = $reflected->getMethod("getId");
        $method->setAccessible(true);

        $this->assertSame(345, $method->invoke($this->track));
    }


    public function testGetMetaData()
    {
        $xml = '<DIDL-Lite ';
            $xml .= 'xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/" ';
            $xml .= 'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
            $xml .= 'xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/" ';
            $xml .= 'xmlns:r="urn:schemas-rinconnetworks-com:metadata-1-0/">';
            $xml .= '<item id="345" parentID="-1" restricted="true">';
                $xml .= '<res></res>';
                $xml .= '<upnp:albumArtURI></upnp:albumArtURI>';
                $xml .= '<dc:title>TITLE</dc:title>';
                $xml .= '<upnp:class>object.item.audioItem.musicTrack</upnp:class>';
                $xml .= '<dc:creator>ARTIST</dc:creator>';
                $xml .= '<upnp:album>ALBUM</upnp:album>';
            $xml .= '</item>';
        $xml .= '</DIDL-Lite>';
        $this->assertSame($xml, $this->track->getMetadata());
    }
}
