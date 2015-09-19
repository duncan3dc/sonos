---
layout: default
title: Tracks
permalink: /usage/tracks/
api: Tracks.Track
---

When working with Queues and Playlists you will often be dealing with instances of the Track class.  
These are very basic objects with public properties:

~~~php
# Get the name of the song
echo $track->title;

# The performing artist
echo $track->artist;

# The album it is from
echo $track->album;

# It's position on that album
echo $track->number;

# The album art (if available)
echo "<img src='{$track->albumArt}'>";
~~~


They can be used with the addTrack(s) methods:

~~~php
# Simple string version
$queue->addTrack("x-file-cifs://LEMIEUX/music/dgm/frame/01-Hereafter.mp3");

# Using a track object
$track = new Track("x-file-cifs://LEMIEUX/music/dgm/frame/01-Hereafter.mp3")
$queue->addTrack($track);
~~~


If you want to pass a custom object to addTrack(s) then your class must implement the <a href='{{ site.baseurl }}/api/classes/duncan3dc.Sonos.Tracks.UriInterface.html'>UriInterface</a>:

~~~php
class Smb implements \duncan3dc\Sonos\Tracks\UriInterface
{
    public $file;
    public funcion __construct($file)
    {
        $this->file = $file;
    }
    public function getUri()
    {
        return "x-file-cifs://LEMIEUX/music/{$this->file}";
    }
}
$tracks = [];
$tracks[] = new Smb("blitz kids/the good youth/09-Pinnacle.mp3");
$tracks[] = new Smb("afi/crash love/03-End transmission.mp3");
$playlist->addTracks($tracks);
~~~


## State Details

When getting state details from a controller the result is similar to the track class.  
All of the public properties at the top of this page are available, as well as:

~~~php
$state = $controller->getStateDetails();

# The same as $track->number, but with a more appropriate name
echo $state->trackNumber;

# If a stream is currently being played, then the string reported by the stream (otherwise null)
echo $state->stream;

# The current (zero-based) position in the controllers queue
echo $state->queueNumber;

# The duration of the currently active track (hh:mm:ss)
echo $state->duration;

# The position of the currently active track (hh:mm:ss)
echo $state->position;
~~~
