---
layout: default
title: Tracks
permalink: /usage/tracks/
api: Interfaces.TrackInterface
---

When working with Queues and Playlists you will often be dealing with instances of the Track class.  
These are very basic objects with public properties:

```php
# Get the name of the song
echo $track->getTitle();

# The performing artist
echo $track->getArtist();

# The album it is from
echo $track->getAlbum();

# It's position on that album
echo $track->getNumber();

# The album art (if available)
echo "<img src='" . $track->getAlbumArt() . "'>";
```


They can be used with the addTrack(s) methods:

```php
# Simple string version
$queue->addTrack("x-file-cifs://LEMIEUX/music/dgm/frame/01-Hereafter.mp3");

# Using a track object
$track = new Track("x-file-cifs://LEMIEUX/music/dgm/frame/01-Hereafter.mp3")
$queue->addTrack($track);
```


If you want to pass a custom object to addTrack(s) then your class must implement the <a href='{{ site.baseurl }}/api/classes/duncan3dc.Sonos.Interfaces.UriInterface.html'>UriInterface</a>:

```php
class Smb implements \duncan3dc\Sonos\Interfaces\UriInterface
{
    private $file;
    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function getUri(): string
    {
        $parts = explode("/", $this->file);
        $parts = array_map("rawurlencode", $parts);
        return "x-file-cifs://kessel/sonos/" . implode("/", $parts);
    }

    public function getMetaData(): string
    {
        return \duncan3dc\Sonos\Helper::createMetaDataXml("-1", "-1", [
            "res"               =>  $this->getUri(),
            "upnp:albumArtURI"  =>  "",
            "dc:title"          =>  "Title",
            "upnp:class"        =>  "object.item.audioItem.musicTrack",
            "dc:creator"        =>  "Artist",
            "upnp:album"        =>  "Album",
        ]);
    }
}

$tracks = [];
$tracks[] = new Smb("blitz kids/the good youth/09-Pinnacle.mp3");
$tracks[] = new Smb("afi/crash love/03-End transmission.mp3");
$playlist->addTracks($tracks);
```


## State Details

When getting state details from a controller the result is similar to the track class.  
The available methods can be seen on the [ControllerStateInterface](../..//api/classes/duncan3dc.Sonos.Interfaces.ControllerStateInterface.html)

```php
$state = $controller->getStateDetails();

# If a stream is being played, then get the stream identifier (otherwise null)
echo $state->getStream();

# The current (zero-based) position in the controllers queue
echo $state->getTrack();

# The position of the currently active track (hh:mm:ss)
echo $state->getPosition();
```
