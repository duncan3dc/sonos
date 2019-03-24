<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\Interfaces\UriInterface;
use duncan3dc\Sonos\Playlist;
use duncan3dc\Sonos\Queue;
use Mockery;

class QueueTest extends MockTest
{
    protected $device;
    protected $controller;
    protected $queue;

    public function setUp()
    {
        parent::setUp();

        $this->device = $this->getDevice();
        $this->controller = $this->getController($this->device);
        $this->queue = new Queue($this->controller);
    }

    public function tearDown()
    {
        Mockery::close();
    }


    protected function mockUpdateId()
    {
        $class = new \ReflectionClass($this->queue);

        $property = $class->getProperty("updateId");
        $property->setAccessible(true);

        $property->setValue($this->queue, 87);
    }


    public function testCount()
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


    public function testGetTracks()
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


    public function testGetTracksInvalidStart()
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
        $playlist = new Playlist("SQ:487", $this->controller);

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

        $this->device->shouldReceive("soap")->once()->with("AVTransport", "AddURIToQueue", [
            "UpdateID" => 0,
            "EnqueuedURI" => "file:///jffs/settings/savedqueues.rsq#487",
            "EnqueuedURIMetaData" => "",
            "DesiredFirstTrackNumberEnqueued" => 1,
            "EnqueueAsNext" => 0,
            "ObjectID" => "Q:0",
        ]);

        $result = $this->queue->addTrack($playlist);
        $this->assertSame($result, $this->queue);
    }


    public function testRemoveTrack()
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


    public function testRemoveTracks()
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


    public function testClear()
    {
        $this->device->shouldReceive("soap")->once()->with("AVTransport", "RemoveAllTracksFromQueue", [
            "ObjectID"          =>  "Q:0",
        ]);

        $this->assertSame($this->queue, $this->queue->clear());
    }
}
