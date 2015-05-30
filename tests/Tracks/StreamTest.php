<?php

namespace duncan3dc\Sonos\Test\Tracks;

use duncan3dc\Sonos\Tracks\Stream;

class StreamTest extends \PHPUnit_Framework_TestCase
{

    public function testGetUri()
    {
        $stream = new Stream("uri-test");
        $this->assertSame("uri-test", $stream->getUri());
    }


    public function testGetName1()
    {
        $stream = new Stream("uri-test");
        $this->assertSame("", $stream->getName());
    }
    public function testGetName2()
    {
        $stream = new Stream("uri-test", "super stream");
        $this->assertSame("super stream", $stream->getName());
    }


    public function testGetMetaData1()
    {
        $stream = new Stream("uri-test");

        $xml = '<DIDL-Lite ';
            $xml .= 'xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/" ';
            $xml .= 'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
            $xml .= 'xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/" ';
            $xml .= 'xmlns:r="urn:schemas-rinconnetworks-com:metadata-1-0/">';
            $xml .= '<item id="-1" parentID="-1" restricted="true">';
                $xml .= '<dc:title>Stream</dc:title>';
                $xml .= '<upnp:class>object.item.audioItem.audioBroadcast</upnp:class>';
                $xml .= '<desc id="cdudn" nameSpace="urn:schemas-rinconnetworks-com:metadata-1-0/">SA_RINCON65031_</desc>';
            $xml .= '</item>';
        $xml .= '</DIDL-Lite>';
        $this->assertSame($xml, $stream->getMetadata());
    }
    public function testGetMetaData2()
    {
        $stream = new Stream("uri-test", "super stream");

        $xml = '<DIDL-Lite ';
            $xml .= 'xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/" ';
            $xml .= 'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
            $xml .= 'xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/" ';
            $xml .= 'xmlns:r="urn:schemas-rinconnetworks-com:metadata-1-0/">';
            $xml .= '<item id="-1" parentID="-1" restricted="true">';
                $xml .= '<dc:title>super stream</dc:title>';
                $xml .= '<upnp:class>object.item.audioItem.audioBroadcast</upnp:class>';
                $xml .= '<desc id="cdudn" nameSpace="urn:schemas-rinconnetworks-com:metadata-1-0/">SA_RINCON65031_</desc>';
            $xml .= '</item>';
        $xml .= '</DIDL-Lite>';
        $this->assertSame($xml, $stream->getMetadata());
    }
}
