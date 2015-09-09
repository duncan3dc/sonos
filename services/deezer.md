---
layout: default
title: Deezer
permalink: /services/deezer/
api: Tracks.Deezer
---

<p class="message-info">This feature was added in v1.6.0</p>

Deezer tracks are handled by the `Deezer` class, which can be created like so:

~~~php
use duncan3dc\Sonos\Tracks\Deezer;

$track = new Deezer("62898679");
~~~

From there it works like any regular track and can be added to the queue like so:

~~~php
$track = new Deezer("62898679");
$controller->getQueue()->addTrack($track);
~~~
