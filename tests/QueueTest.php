<?php

namespace duncan3dc\SonosTests;

use duncan3dc\DomParser\XmlElement;
use duncan3dc\Sonos\Controller;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use duncan3dc\Sonos\Interfaces\UriInterface;
use duncan3dc\Sonos\Playlist;
use duncan3dc\Sonos\Queue;
use Mockery;
use Mockery\MockInterface;

class QueueTest extends MockTest
{
    /** @var DeviceInterface&MockInterface  */
    protected $device;

    /** @var Controller */
    protected $controller;

    /** @var Queue */
    protected $queue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->device = $this->getDevice();
        $this->controller = $this->getController($this->device);
        $this->queue = new Queue($this->controller);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }


    protected function mockUpdateId(): void
    {
        $class = new \ReflectionClass($this->queue);

        $property = $class->getProperty("updateId");
        $property->setAccessible(true);

        $property->setValue($this->queue, 87);
    }


    public function testCount(): void
    {
        $this->device->shouldReceive("soap")->twice()->with("ContentDirectory", "Browse", [
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  1,
            "Filter"            =>  "",
            "SortCriteria"      =>  "",
            "ObjectID"          =>  "Q:0",
        ])->andReturn([
            "TotalMatches"      =>  "3",
        ]);

        $this->assertSame(3, $this->queue->count());
        $this->assertSame(3, count($this->queue));
    }


    public function testGetTracks(): void
    {
        $this->device->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  2,
            "Filter"            =>  "",
            "SortCriteria"      =>  "",
            "ObjectID"          =>  "Q:0",
        ])->andReturn([
            "Result"            =>  "<items><item></item><item></item><item></item><item></item><item></item></items>",
            "NumberReturned"    =>  2,
            "TotalMatches"      =>  10,
        ]);

        $tracks = $this->queue->getTracks(0, 2);
        $this->assertSame(2, count($tracks));
    }


    public function testGetTracksInvalidStart(): void
    {
        $this->device->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "StartingIndex"     =>  5,
            "RequestedCount"    =>  2,
            "Filter"            =>  "",
            "SortCriteria"      =>  "",
            "ObjectID"          =>  "Q:0",
        ])->andReturn([
            "Result"            =>  "<items></items>",
            "NumberReturned"    =>  0,
            "TotalMatches"      =>  10,
        ]);

        $tracks = $this->queue->getTracks(5, 2);
        $this->assertSame(0, count($tracks));
    }


    /**
     * Ensure we can add a track.
     */
    public function testAddTrack1(): void
    {
        $track = Mockery::mock(UriInterface::class);
        $track->shouldReceive("getUri")->once()->with()->andReturn("uri://example-file.mp3");
        $track->shouldReceive("getMetaData")->once()->with()->andReturn("<DIDL-Lite></DIDL-Lite>");

        $this->device->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "BrowseFlag" => "BrowseDirectChildren",
            "StartingIndex" => 0,
            "RequestedCount" => 1,
            "Filter" => "",
            "SortCriteria" => "",
            "ObjectID" => "Q:0",
        ])->andReturn([
            "UpdateID" => 123,
            "TotalMatches" => 7,
        ]);

        $this->device->shouldReceive("soap")->once()->with("AVTransport", "AddURIToQueue", [
            "UpdateID" => 0,
            "EnqueuedURI" => "uri://example-file.mp3",
            "EnqueuedURIMetaData" => "<DIDL-Lite></DIDL-Lite>",
            "DesiredFirstTrackNumberEnqueued" => 8,
            "EnqueueAsNext" => 0,
            "ObjectID" => "Q:0",
        ]);

        $result = $this->queue->addTrack($track);
        $this->assertSame($result, $this->queue);
    }


    /**
     * Ensure we can add a playlist.
     */
    public function testAddTrack2(): void
    {
        $xml = Mockery::mock(XmlElement::class);
        $xml->shouldReceive("getAttribute")->once()->with("id")->andReturn("SQ:487");
        $xml->shouldReceive("getTag")->once()->with("title")->andReturn((object) ["nodeValue" => "Good Songs"]);

        $playlist = new Playlist($xml, $this->controller);

        $this->device->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "BrowseFlag" => "BrowseDirectChildren",
            "StartingIndex" => 0,
            "RequestedCount" => 1,
            "Filter" => "",
            "SortCriteria" => "",
            "ObjectID" => "Q:0",
        ])->andReturn([
            "UpdateID" => 123,
            "TotalMatches" => 0,
        ]);

        $xml = '<DIDL-Lite ';
        $xml .= 'xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/" ';
        $xml .= 'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
        $xml .= 'xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/" ';
        $xml .= 'xmlns:r="urn:schemas-rinconnetworks-com:metadata-1-0/">';
        $xml .= '<item id="SQ:487" parentID="SQ:" restricted="true">';
        $xml .= '<dc:title>Good Songs</dc:title>';
        $xml .= '<upnp:class>object.container.playlistContainer</upnp:class>';
        $xml .= '<desc ';
        $xml .= 'id="cdudn" ';
        $xml .= 'nameSpace="urn:schemas-rinconnetworks-com:metadata-1-0/"';
        $xml .= '>RINCON_AssociatedZPUDN</desc>';
        $xml .= '</item>';
        $xml .= '</DIDL-Lite>';

        $this->device->shouldReceive("soap")->once()->with("AVTransport", "AddURIToQueue", [
            "UpdateID" => 0,
            "EnqueuedURI" => "file:///jffs/settings/savedqueues.rsq#487",
            "EnqueuedURIMetaData" => $xml,
            "DesiredFirstTrackNumberEnqueued" => 1,
            "EnqueueAsNext" => 0,
            "ObjectID" => "Q:0",
        ]);

        $result = $this->queue->addTrack($playlist);
        $this->assertSame($result, $this->queue);
    }


    public function testRemoveTrack(): void
    {
        $this->device->shouldReceive("soap")->once()->with("AVTransport", "RemoveTrackRangeFromQueue", [
            "UpdateID"          =>  87,
            "StartingIndex"     =>  8,
            "NumberOfTracks"    =>  1,
            "ObjectID"          =>  "Q:0",
        ])->andReturn(88);

        $this->mockUpdateId();
        $this->assertTrue($this->queue->removeTrack(7));
    }


    public function testRemoveTracks(): void
    {
        $this->device->shouldReceive("soap")->once()->with("AVTransport", "RemoveTrackRangeFromQueue", [
            "UpdateID"          =>  87,
            "StartingIndex"     =>  4,
            "NumberOfTracks"    =>  2,
            "ObjectID"          =>  "Q:0",
        ])->andReturn(88);
        $this->device->shouldReceive("soap")->once()->with("AVTransport", "RemoveTrackRangeFromQueue", [
            "UpdateID"          =>  88,
            "StartingIndex"     =>  5,
            "NumberOfTracks"    =>  2,
            "ObjectID"          =>  "Q:0",
        ])->andReturn(89);

        $this->mockUpdateId();
        $this->assertTrue($this->queue->removeTracks([3, 4, 6, 7]));
    }


    public function testClear(): void
    {
        $this->device->shouldReceive("soap")->once()->with("AVTransport", "RemoveAllTracksFromQueue", [
            "ObjectID"          =>  "Q:0",
        ]);

        $this->assertSame($this->queue, $this->queue->clear());
    }
}
