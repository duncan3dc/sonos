---
layout: default
title: Spotify
permalink: /services/spotify/
api: Tracks.Spotify
---

<p class="message-info">This feature was added in v1.6.0</p>

Spotify tracks are handled by the `Spotify` class, which can be created like so:

```php
use duncan3dc\Sonos\Tracks\Spotify;

$track = new Spotify("2c9TM5qY2Kx330wuh4O72y");
```

From there it works like any regular track and can be added to the queue like so:

```php
$track = new Spotify("2c9TM5qY2Kx330wuh4O72y");
$controller->getQueue()->addTrack($track);
```

By default the class is setup for the european Spotify service, if you are in the US you can set the region like so:

```php
Spotify::$region = Spotify::REGION_US;
```
