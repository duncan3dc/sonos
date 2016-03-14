---
layout: default
title: Playlists
permalink: /usage/playlists/
api: Playlist
---

You can get existing playlists from the [Network class](../getting-started/#playlists) or create a new one like so:

```php
$playlist = $sonos->createPlaylist("Daniel Tompkins");
echo "{$playlist->getName()}\n";

# Changed my mind, delete the playlist from the network
$playlist->delete();
```


The Playlist class implements the Countable interface which means you can get the number of tracks by simply counting it:

```php
$numberOfTracks = count($playlist);

# Or call the actual count method
$numberOfTracks = $playlist->count();
```


You can empty a playlist using the clear method:

```php
$playlist->clear();
```


You can list all the tracks from a playlist:

```php
$playlist = $sonos->getPlaylistByName("progmetal");

foreach ($playlist->getTracks() as $track) {
    echo "* {$track->artist} - {$track->title}\n";
}
```
<p class="message-info">The getTracks() method returns an array of <a href='../tracks/'>Tracks</a>.</p>



Remove tracks from a playlist:

```php
$progmetal = $sonos->getPlaylistByName("progmetal");

$remove = [];
foreach ($progmetal->getTracks() as $position => $track) {
    if ($track->artist === "protest the hero") {
        $remove[] = $position;
    }
}
if (count($remove) > 0) {
    $progmetal->removeTracks($remove);
}
```
<p class="message-info">This is done using a single call to removeTracks() because all the positions will be recalculated once a track has been removed, so the other positions would now be invalid. It's also more efficient as we only send one request to the Sonos network instead of many.</p>
