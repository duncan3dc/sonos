<?php

namespace duncan3dc\SonosTests;

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
            "TotalMatches"      =>  10,
        ]);

        $tracks = $this->queue->getTracks(0, 2);
        $this->assertSame(2, count($tracks));
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
