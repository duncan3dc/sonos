---
layout: default
title: Announcements
permalink: /fun/announcements/
---

<p class="message-info">This feature was added in v1.2.0</p>

The Sonos library supports making announcements utilising the Google [text to speech](../text-to-speech/) api.

First you need to have a [SMB](https://en.wikipedia.org/wiki/Server_Message_Block) share available:

~~~php
 use duncan3dc\Sonos\Directory;

 # The path on the local file system to store audio files
 $localPath = "/var/shares/sonos";

 # The SMB server (this must be in uppercase, and using an IP has been reported not to work)
 $hostname = "LEMIEUX";

 # The share name that is setup for the local path
 $smb = "{$hostname}/sonos",

 # The final argument is the name of the directory that this object is for
 $directory = new Directory($localPath, $smb, "tts");
~~~


Once you have a `Directory` instance, you can then create audio messages using text to speech:

~~~php
 use duncan3dc\Sonos\Tracks\TextToSpeech;

 $track = new TextToSpeech("Testing, testing, 123", $directory);
~~~


You can use these just like any other [track](../../usage/tracks/), however the most interesting thing to do is interrupt the current activity with a message, and then resume playing again.

~~~php
 $bedroom = $sonos->getControllerByRoom("Bedroom");

 $bedroom->interrupt($track);
~~~


This will backup the current state of the controller (queue contents, stream details, volume, equalisation, etc), play the passed `$track`, and then restore the previous state, effectively interrupting the music to make an announcement and then carrying on where the music left off.
