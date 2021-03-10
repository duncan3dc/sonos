<?php

namespace duncan3dc\SonosTests\Services;

use duncan3dc\Sonos\Exceptions\NotFoundException;
use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\Services\Radio;
use duncan3dc\Sonos\Tracks\Stream;
use duncan3dc\SonosTests\MockTest;
use Mockery;
use Mockery\MockInterface;

class RadioTest extends MockTest
{
    /** @var ControllerInterface|MockInterface */
    private $controller;

    /** @var Radio */
    private $radio;


    protected function setUp()
    {
        parent::setUp();

        $this->controller = Mockery::mock(ControllerInterface::class);
        $this->radio = new Radio($this->controller);
    }


    public function testGetFavouriteStations()
    {
        $this->controller->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "ObjectID"          =>  "R:0/0",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "*",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  100,
            "SortCriteria"      =>  "",
        ])->andReturn([
            "Result"    =>  "<item><title>Station 1</title><res>URI</res></item>",
        ]);

        $result = $this->radio->getFavouriteStations();

        $this->assertEquals([new Stream("URI", "Station 1")], $result);
    }


    public function testGetFavouriteStationExact()
    {
        $this->controller->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "ObjectID"          =>  "R:0/0",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "*",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  100,
            "SortCriteria"      =>  "",
        ])->andReturn([
            "Result"    =>  "<item><title>Station 1</title><res>URI</res></item>",
        ]);

        $result = $this->radio->getFavouriteStation("Station 1");

        $this->assertEquals(new Stream("URI", "Station 1"), $result);
    }


    public function testGetFavouriteStationRough()
    {
        $this->controller->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "ObjectID"          =>  "R:0/0",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "*",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  100,
            "SortCriteria"      =>  "",
        ])->andReturn([
            "Result"    =>  "<item><title>Station 1</title><res>URI</res></item>",
        ]);

        $result = $this->radio->getFavouriteStation("STATION 1");

        $this->assertEquals(new Stream("URI", "Station 1"), $result);
    }



    public function testGetFavouriteStationFail()
    {
        $this->controller->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "ObjectID"          =>  "R:0/0",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "*",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  100,
            "SortCriteria"      =>  "",
        ])->andReturn([
            "Result"    =>  "<item><title>Station 1</title><res>URI</res></item>",
        ]);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Unable to find a radio station by the name 'Station 2'");
        $this->radio->getFavouriteStation("Station 2");
    }


    public function testGetFavouriteShows()
    {
        $this->controller->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "ObjectID"          =>  "R:0/1",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "*",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  100,
            "SortCriteria"      =>  "",
        ])->andReturn([
            "Result"    =>  "<container><title>Show 1</title><res>URI</res></container>",
        ]);

        $result = $this->radio->getFavouriteShows();

        $this->assertEquals([new Stream("URI", "Show 1")], $result);
    }


    public function testGetFavouriteShowExact()
    {
        $this->controller->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "ObjectID"          =>  "R:0/1",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "*",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  100,
            "SortCriteria"      =>  "",
        ])->andReturn([
            "Result"    =>  "<container><title>Show 1</title><res>URI</res></container>",
        ]);

        $result = $this->radio->getFavouriteShow("Show 1");

        $this->assertEquals(new Stream("URI", "Show 1"), $result);
    }


    public function testGetFavouriteShowRough()
    {
        $this->controller->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "ObjectID"          =>  "R:0/1",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "*",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  100,
            "SortCriteria"      =>  "",
        ])->andReturn([
            "Result"    =>  "<container><title>Show 1</title><res>URI</res></container>",
        ]);

        $result = $this->radio->getFavouriteShow("show 1");

        $this->assertEquals(new Stream("URI", "Show 1"), $result);
    }



    public function testGetFavouriteShowFail()
    {
        $this->controller->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "ObjectID"          =>  "R:0/1",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "*",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  100,
            "SortCriteria"      =>  "",
        ])->andReturn([
            "Result"    =>  "<container><title>Show 1</title><res>URI</res></container>",
        ]);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage("Unable to find a radio show by the name 'Show 2'");
        $this->radio->getFavouriteShow("Show 2");
    }
}
