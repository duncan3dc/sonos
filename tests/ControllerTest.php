<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\Controller;
use duncan3dc\Sonos\Exceptions\SoapException;
use duncan3dc\Sonos\Interfaces\ControllerStateInterface;
use duncan3dc\Sonos\Utils\Time;
use Mockery;

class ControllerTest extends MockTest
{
    public function testPlay(): void
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Play", [
            "Speed" =>  1,
        ]);

        $this->assertSame($controller, $controller->play());
    }


    public function testPlayEmptyQueue(): void
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);
        $exception = Mockery::mock(SoapException::class);

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

        $this->expectException(\BadMethodCallException::class);
        $controller->play();
    }



    public function testSelectTrack(): void
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "TRACK_NR",
            "Target"    =>  4,
        ]);

        $this->assertSame($controller, $controller->selectTrack(3));
    }


    public function testSeekSeconds(): void
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "REL_TIME",
            "Target"    =>  "00:00:55",
        ]);

        $this->assertSame($controller, $controller->seek(Time::inSeconds(55)));
    }


    public function testSeekMinutes(): void
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "REL_TIME",
            "Target"    =>  "00:02:02",
        ]);

        $this->assertSame($controller, $controller->seek(Time::inSeconds(122)));
    }


    public function testSeekHours(): void
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "REL_TIME",
            "Target"    =>  "01:05:00",
        ]);

        $this->assertSame($controller, $controller->seek(Time::parse("1:5:0")));
    }


    public function testSeekZero(): void
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "REL_TIME",
            "Target"    =>  "00:00:00",
        ]);

        $this->assertSame($controller, $controller->seek(Time::start()));
    }


    public function testRestoreState(): void
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $state = Mockery::mock(ControllerStateInterface::class);
        $state->shouldReceive("getState")->once()->with()->andReturn(Controller::STATE_STOPPED);
        $state->shouldReceive("getRepeat")->once()->with()->andReturn(false);
        $state->shouldReceive("getShuffle")->once()->with()->andReturn(false);
        $state->shouldReceive("getCrossfade")->once()->with()->andReturn(false);
        $state->shouldReceive("getSpeakers")->once()->with()->andReturn([]);
        $state->shouldReceive("getTracks")->once()->with()->andReturn([]);
        $state->shouldReceive("getStream")->once()->with()->andReturn(null);

        $device
            ->shouldReceive("soap")
            ->once()
            ->with("AVTransport", "RemoveAllTracksFromQueue", ["ObjectID" => "Q:0"]);
        $device
            ->shouldReceive("soap")
            ->once()
            ->with("AVTransport", "GetTransportSettings", [])
            ->andReturn(["PlayMode" => "TEST"]);
        $device
            ->shouldReceive("soap")
            ->once()
            ->with("AVTransport", "SetCrossfadeMode", ["CrossfadeMode" => false]);

        $device
            ->shouldReceive("soap")
            ->once()
            ->with("AVTransport", "GetTransportInfo", [])
            ->andReturn(["CurrentTransportState" => "TEST"]);
        $device
            ->shouldReceive("soap")
            ->once()
            ->with("AVTransport", "GetTransportSettings", [])
            ->andReturn(["PlayMode" => "TEST"]);

        $result = $controller->restoreState($state);
        $this->assertSame($controller, $result);
    }


    public function testRestoreStateWithTracks(): void
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $state = Mockery::mock(ControllerStateInterface::class);
        $state->shouldReceive("getState")->once()->with()->andReturn(Controller::STATE_STOPPED);
        $state->shouldReceive("getTrack")->once()->with()->andReturn(0);
        $state->shouldReceive("getPosition")->once()->with()->andReturn(Time::parse("05:03:01"));
        $state->shouldReceive("getRepeat")->once()->with()->andReturn(false);
        $state->shouldReceive("getShuffle")->once()->with()->andReturn(false);
        $state->shouldReceive("getCrossfade")->once()->with()->andReturn(false);
        $state->shouldReceive("getSpeakers")->once()->with()->andReturn([]);
        $state->shouldReceive("getTracks")->once()->with()->andReturn(["track"]);
        $state->shouldReceive("getStream")->once()->with()->andReturn(null);

        $device
            ->shouldReceive("soap")
            ->once()
            ->with("AVTransport", "RemoveAllTracksFromQueue", ["ObjectID" => "Q:0"]);
        $device
            ->shouldReceive("soap")
            ->once()
            ->with("AVTransport", "GetTransportSettings", [])
            ->andReturn(["PlayMode" => "TEST"]);
        $device
            ->shouldReceive("soap")
            ->once()
            ->with("AVTransport", "SetCrossfadeMode", ["CrossfadeMode" => false]);

        $device->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  1,
            "Filter"            =>  "",
            "SortCriteria"      =>  "",
            "ObjectID"          =>  "Q:0",
        ]);
        $device
            ->shouldReceive("soap")
            ->once()
            ->with("AVTransport", "AddMultipleURIsToQueue", Mockery::any())
            ->andReturn(["NumTracksAdded" => 1, "NewUpdateID" => 86]);
        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "TRACK_NR",
            "Target"    =>  1,
        ]);
        $device->shouldReceive("soap")->once()->with("AVTransport", "Seek", [
            "Unit"      =>  "REL_TIME",
            "Target"    =>  "05:03:01",
        ]);

        $device
            ->shouldReceive("soap")
            ->once()
            ->with("AVTransport", "GetTransportInfo", [])
            ->andReturn(["CurrentTransportState" => "TEST"]);
        $device
            ->shouldReceive("soap")
            ->once()
            ->with("AVTransport", "GetTransportSettings", [])
            ->andReturn(["PlayMode" => "TEST"]);

        $result = $controller->restoreState($state);
        $this->assertSame($controller, $result);
    }


    public function testGetStateDetailsQueue(): void
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $xml = '<DIDL-Lite ';
            $xml .= 'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
            $xml .= 'xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/" ';
            $xml .= 'xmlns:r="urn:schemas-rinconnetworks-com:metadata-1-0/" ';
            $xml .= 'xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/"';
        $xml .= '>';
            $xml .= '<item id="-1" parentID="-1" restricted="true">';
                $xml .= '<res protocolInfo="x-file-cifs:*:audio/mpeg:*" duration="0:04:04">';
                    $xml .= 'x-file-cifs://LEMIEUX/sonos/afi/burials/12-Anxious.mp3';
                $xml .= '</res>';
                $xml .= '<r:streamContent></r:streamContent>';
                $xml .= '<dc:title>anxious</dc:title>';
                $xml .= '<upnp:class>object.item.audioItem.musicTrack</upnp:class>';
                $xml .= '<dc:creator>afi</dc:creator>';
                $xml .= '<upnp:album>burials</upnp:album>';
                $xml .= '<upnp:originalTrackNumber>1</upnp:originalTrackNumber>';
                $xml .= '<r:albumArtist>afi</r:albumArtist>';
            $xml .= '</item>';
        $xml .= '</DIDL-Lite>';

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetPositionInfo", [])->andReturn([
            "Track"         =>  1,
            "TrackDuration" =>  "0:04:04",
            "TrackMetaData" =>  $xml,
            "TrackURI"      =>  "x-file-cifs://LEMIEUX/sonos/afi/burials/12-Anxious.mp3",
            "RelTime"       =>  "0:00:15",
        ]);

        $state = $controller->getStateDetails();

        $this->assertSame("x-file-cifs://LEMIEUX/sonos/afi/burials/12-Anxious.mp3", $state->getUri());
        $this->assertSame("anxious", $state->getTitle());
        $this->assertSame("afi", $state->getArtist());
        $this->assertSame("burials", $state->getAlbum());
        $this->assertSame(0, $state->getNumber());
        $this->assertSame("00:04:04", $state->getDuration()->asString());
        $this->assertSame("00:00:15", $state->getPosition()->asString());
        $this->assertNull($state->getStream());
    }


    public function testGetStateDetailsStream(): void
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $xml = '<DIDL-Lite ';
            $xml .= 'xmlns:dc="http://purl.org/dc/elements/1.1/" ';
            $xml .= 'xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/" ';
            $xml .= 'xmlns:r="urn:schemas-rinconnetworks-com:metadata-1-0/" ';
            $xml .= 'xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/"';
        $xml .= '>';
            $xml .= '<item id="-1" parentID="-1" restricted="true">';
                $xml .= '<res protocolInfo="x-rincon-mp3radio:*:*:*">';
                    $xml .= 'x-rincon-mp3radio://tx.sharp-stream.com/http_live.php?i=teamrock.mp3';
                $xml .= '</res>';
                $xml .= '<r:streamContent>New Found Glory - Hit Or Miss</r:streamContent>';
                $xml .= '<dc:title>http_live.php?i=teamrock.mp3</dc:title>';
                $xml .= '<upnp:class>object.item</upnp:class>';
            $xml .= '</item>';
        $xml .= '</DIDL-Lite>';

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetPositionInfo", [])->andReturn([
            "Track"         =>  1,
            "TrackDuration" =>  "0:00:00",
            "TrackMetaData" =>  $xml,
            "TrackURI"      =>  "x-rincon-mp3radio://tx.sharp-stream.com/http_live.php?i=teamrock.mp3",
            "RelTime"       =>  "0:00:02",
        ]);

        $xml = '<DIDL-Lite xmlns:dc="http://purl.org/dc/elements/1.1/" ';
            $xml .= 'xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/" ';
            $xml .= 'xmlns:r="urn:schemas-rinconnetworks-com:metadata-1-0/" ';
            $xml .= 'xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/"';
        $xml .= '>';
            $xml .= '<item id="-1" parentID="-1" restricted="true">';
                $xml .= '<dc:title>TeamRock Radio</dc:title>';
                $xml .= '<upnp:class>object.item.audioItem.audioBroadcast</upnp:class>';
                $xml .= '<desc ';
                    $xml .= 'id="cdudn" ';
                    $xml .= 'nameSpace="urn:schemas-rinconnetworks-com:metadata-1-0/"';
                $xml .= '>SA_RINCON65031_</desc>';
            $xml .= '</item>';
        $xml .= '</DIDL-Lite>';

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetMediaInfo", [])->andReturn([
            "CurrentURI"            =>  "x-sonosapi-stream:s200662?sid=254&flags=8224&sn=0",
            "CurrentURIMetaData"    =>  $xml,
        ]);

        $state = $controller->getStateDetails();

        $this->assertSame("x-rincon-mp3radio://tx.sharp-stream.com/http_live.php?i=teamrock.mp3", $state->getUri());
        $this->assertSame("Hit Or Miss", $state->getTitle());
        $this->assertSame("New Found Glory", $state->getArtist());
        $this->assertSame("00:00:00", $state->getDuration()->asString());
        $this->assertSame("00:00:02", $state->getPosition()->asString());
        $this->assertSame("TeamRock Radio", $state->getStream()->getTitle());
    }


    public function testGetStateDetailsLineIn(): void
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
        $this->assertSame("Line-In", $state->getStream()->getTitle());
    }


    public function testGetStateDetailsEmptyQueue(): void
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
        $this->assertNull($state->getStream());
    }


    public function testIsStreamingQueue(): void
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetMediaInfo", [])->andReturn([
            "CurrentURI"    =>  "x-rincon-queue:RINCON_B8E93759B3D601400#0",
        ]);

        $this->assertFalse($controller->isStreaming());
    }


    public function testIsStreamingStream(): void
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetMediaInfo", [])->andReturn([
            "CurrentURI"    =>  "x-sonosapi-stream:s200662?sid=254&flags=8224&sn=0",
        ]);

        $this->assertTrue($controller->isStreaming());
    }


    public function testIsStreamingAmazon(): void
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetMediaInfo", [])->andReturn([
            "CurrentURI"    =>  "x-sonosapi-radio:s200662?sid=254&flags=8224&sn=0",
        ]);

        $this->assertTrue($controller->isStreaming());
    }


    public function testIsStreamingPlaybar(): void
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "GetMediaInfo", [])->andReturn([
            "CurrentURI"    =>  "x-sonos-htastream:RINCON_5CAAFD0A251401400:spdif",
        ]);

        $this->assertTrue($controller->isStreaming());
    }
}
