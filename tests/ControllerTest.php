<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\ControllerState;
use Mockery;

class ControllerTest extends MockTest
{

    public function testPlay()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Play", [
            "Speed" =>  1,
        ]);

        $this->assertSame($controller, $controller->play());
    }


    public function testPlayEmptyQueue()
    {
        if (defined("HHVM_VERSION")) {
            $this->markTestSkipped("Unable to mock Exceptions on HHVM");
        }

        $device = $this->getDevice();
        $controller = $this->getController($device);
        $exception = Mockery::mock("duncan3dc\Sonos\Exceptions\SoapException");

        $device->shouldReceive("soap")->once()->with("AVTransport", "Play", [
            "Speed" =>  1,
        ])->andThrow($exception);
        $device->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  1,
            "Filter"            =>  "",
            "SortCriteria"      =>  "",
            "ObjectID"          =>  "Q:0",
        ]);

        $this->setExpectedException("\BadMethodCallException");
        $controller->play();
    }



    public function testSelectTrack()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "TRACK_NR",
            "Target"    =>  4,
        ]);

        $this->assertSame($controller, $controller->selectTrack(3));
    }


    public function testSeekSeconds()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "REL_TIME",
            "Target"    =>  "00:00:55",
        ]);

        $this->assertSame($controller, $controller->seek(55));
    }


    public function testSeekMinutes()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "REL_TIME",
            "Target"    =>  "00:02:02",
        ]);

        $this->assertSame($controller, $controller->seek(122));
    }


    public function testSeekHours()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "REL_TIME",
            "Target"    =>  "01:05:00",
        ]);

        $this->assertSame($controller, $controller->seek(3900));
    }


    public function testSeekZero()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "REL_TIME",
            "Target"    =>  "00:00:00",
        ]);

        $this->assertSame($controller, $controller->seek(0));
    }


    public function testRestoreState()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $state = Mockery::mock(ControllerState::class);
        $state->speakers = [];
        $state->tracks = [];

        $device->shouldReceive("soap")->once()->with("AVTransport", "RemoveAllTracksFromQueue", ["ObjectID" => "Q:0"]);
        $device->shouldReceive("soap")->once()->with("AVTransport", "GetTransportSettings", []);
        $device->shouldReceive("soap")->once()->with("AVTransport", "SetCrossfadeMode", ["CrossfadeMode" => false]);

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetTransportInfo", []);
        $device->shouldReceive("soap")->once()->with("AVTransport", "GetTransportSettings", []);

        $controller->restoreState($state);
    }


    public function testRestoreStateWithTracks()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $state = Mockery::mock(ControllerState::class);
        $state->speakers = [];
        $state->tracks = ["track"];
        $state->position = "05:03:01";

        $device->shouldReceive("soap")->once()->with("AVTransport", "RemoveAllTracksFromQueue", ["ObjectID" => "Q:0"]);
        $device->shouldReceive("soap")->once()->with("AVTransport", "GetTransportSettings", []);
        $device->shouldReceive("soap")->once()->with("AVTransport", "SetCrossfadeMode", ["CrossfadeMode" => false]);

        $device->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  1,
            "Filter"            =>  "",
            "SortCriteria"      =>  "",
            "ObjectID"          =>  "Q:0",
        ]);
        $device->shouldReceive("soap")->once()->with("AVTransport", "AddMultipleURIsToQueue", Mockery::any());
        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "TRACK_NR",
            "Target"    =>  1,
        ]);
        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "REL_TIME",
            "Target"    =>  "05:03:01",
        ]);

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetTransportInfo", []);
        $device->shouldReceive("soap")->once()->with("AVTransport", "GetTransportSettings", []);

        $controller->restoreState($state);
    }


    public function testGetStateDetailsQueue()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetPositionInfo", [])->andReturn([
            "Track"         =>  1,
            "TrackDuration" =>  "0:04:04",
            "TrackMetaData" =>  '<DIDL-Lite xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/" xmlns:r="urn:schemas-rinconnetworks-com:metadata-1-0/" xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/"><item id="-1" parentID="-1" restricted="true"><res protocolInfo="x-file-cifs:*:audio/mpeg:*" duration="0:04:04">x-file-cifs://LEMIEUX/sonos/the%20used/imaginary%20enemy/01-Revolution.mp3</res><r:streamContent></r:streamContent><dc:title>revolution</dc:title><upnp:class>object.item.audioItem.musicTrack</upnp:class><dc:creator>the used</dc:creator><upnp:album>imaginary enemy</upnp:album><upnp:originalTrackNumber>1</upnp:originalTrackNumber><r:albumArtist>the used</r:albumArtist></item></DIDL-Lite>',
            "TrackURI"      =>  "x-file-cifs://LEMIEUX/sonos/the%20used/imaginary%20enemy/01-Revolution.mp3",
            "RelTime"       =>  "0:00:15",
        ]);

        $state = $controller->getStateDetails();

        $this->assertSame("x-file-cifs://LEMIEUX/sonos/the%20used/imaginary%20enemy/01-Revolution.mp3", $state->getUri());
        $this->assertSame("revolution", $state->title);
        $this->assertSame("the used", $state->artist);
        $this->assertSame("imaginary enemy", $state->album);
        $this->assertSame(1, $state->trackNumber);
        $this->assertSame(0, $state->queueNumber);
        $this->assertSame("0:04:04", $state->duration);
        $this->assertSame("0:00:15", $state->position);
        $this->assertNull($state->stream);
    }


    public function testGetStateDetailsStream()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetPositionInfo", [])->andReturn([
            "Track"         =>  1,
            "TrackDuration" =>  "0:00:00",
            "TrackMetaData" =>  '<DIDL-Lite xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/" xmlns:r="urn:schemas-rinconnetworks-com:metadata-1-0/" xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/"><item id="-1" parentID="-1" restricted="true"><res protocolInfo="x-rincon-mp3radio:*:*:*">x-rincon-mp3radio://tx.sharp-stream.com/http_live.php?i=teamrock.mp3</res><r:streamContent>New Found Glory - Hit Or Miss</r:streamContent><dc:title>http_live.php?i=teamrock.mp3</dc:title><upnp:class>object.item</upnp:class></item></DIDL-Lite>',
            "TrackURI"      =>  "x-rincon-mp3radio://tx.sharp-stream.com/http_live.php?i=teamrock.mp3",
            "RelTime"       =>  "0:00:02",
        ]);

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetMediaInfo", [])->andReturn([
            "CurrentURI"            =>  "x-sonosapi-stream:s200662?sid=254&flags=8224&sn=0",
            "CurrentURIMetaData"    =>  '<DIDL-Lite xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/" xmlns:r="urn:schemas-rinconnetworks-com:metadata-1-0/" xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/"><item id="-1" parentID="-1" restricted="true"><dc:title>TeamRock Radio</dc:title><upnp:class>object.item.audioItem.audioBroadcast</upnp:class><desc id="cdudn" nameSpace="urn:schemas-rinconnetworks-com:metadata-1-0/">SA_RINCON65031_</desc></item></DIDL-Lite>',
        ]);

        $state = $controller->getStateDetails();

        $this->assertSame("x-rincon-mp3radio://tx.sharp-stream.com/http_live.php?i=teamrock.mp3", $state->getUri());
        $this->assertSame("Hit Or Miss", $state->title);
        $this->assertSame("New Found Glory", $state->artist);
        $this->assertSame("0:00:00", $state->duration);
        $this->assertSame("0:00:02", $state->position);
        $this->assertSame("TeamRock Radio", $state->stream);
    }


    public function testGetStateDetailsLineIn()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetPositionInfo", [])->andReturn([
            "Track"         =>  1,
            "TrackDuration" =>  "NOT_IMPLEMENTED",
            "TrackMetaData" =>  "NOT_IMPLEMENTED",
            "TrackURI"      =>  "x-rincon-stream:RINCON_B8E9372C898401400",
            "RelTime"       =>  "NOT_IMPLEMENTED",
        ]);

        $state = $controller->getStateDetails();

        $this->assertSame("x-rincon-stream:RINCON_B8E9372C898401400", $state->getUri());
        $this->assertSame("Line-In", $state->stream);
    }


    public function testGetStateDetailsEmptyQueue()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetPositionInfo", [])->andReturn([
            "Track"         =>  0,
            "TrackDuration" =>  "0:00:00",
            "TrackMetaData" =>  "",
            "TrackURI"      =>  "",
            "RelTime"       =>  "0:00:00",
        ]);

        $state = $controller->getStateDetails();

        $this->assertSame("", $state->getUri());
        $this->assertNull($state->stream);
    }


    public function testIsStreamingQueue()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetMediaInfo", [])->andReturn([
            "CurrentURI"    =>  "x-rincon-queue:RINCON_B8E93759B3D601400#0",
        ]);

        $this->assertFalse($controller->isStreaming());
    }


    public function testIsStreamingStream()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetMediaInfo", [])->andReturn([
            "CurrentURI"    =>  "x-sonosapi-stream:s200662?sid=254&flags=8224&sn=0",
        ]);

        $this->assertTrue($controller->isStreaming());
    }


    public function testIsStreamingLineIn()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetMediaInfo", [])->andReturn([
            "CurrentURI"    =>  "x-rincon-stream:RINCON_5CAAFD0A251401400",
        ]);

        $this->assertTrue($controller->isStreaming());
    }
}
