sonos
=====

PHP classes to control Sonos speakers.

Class documentation available at http://duncan3dc.github.io/sonos/  

[![Build Status](https://travis-ci.org/duncan3dc/sonos.svg?branch=master)](https://travis-ci.org/duncan3dc/sonos)
[![Latest Stable Version](https://poser.pugx.org/duncan3dc/sonos/version.svg)](https://packagist.org/packages/duncan3dc/sonos)

Inspired by [DjMomo/sonos](https://github.com/DjMomo/sonos) and [phil-lavin/sonos](https://github.com/phil-lavin/sonos)


Examples
--------

The classes use a namespace of duncan3dc\Sonos
```php
use duncan3dc\Sonos;
```

It is advisable to switch cache on to speed up the initial network lookup, be aware that you will need to clear this if you add/remove sonos devices from your network
```php
Network::$cache = true;
```

Get all the speakers on the network
```php
$speakers = Sonos\Network::getSpeakers();
foreach($speakers as $speaker) {
    echo $speaker->ip . "\n";
    echo "\t" . $speaker->name . " (" . $speaker->room . ")\n";
}
```

Start all groups playing music
```php
$controllers = Sonos\Network::getControllers();
foreach($controllers as $controller) {
    echo $controller->name . " (" . $controller->room . ")\n";
    echo "\tState: " . $controller->getState() . "\n";
    $controller->play();
}
```

Control what is currently playing in the Living Room, even if it is not the coordinator of it's current group
```php
$controller = Sonos\Network::getControllerByRoom("Living Room");
echo $controller->room . "\n";
$controller->pause();
```

Add all the tracks from one playlist to another
```php
$protest = Sonos\Network::getPlaylistByName("protest the hero");
$progmetal = Sonos\Network::getPlaylistByName("progmetal");

foreach($protest->getTracks() as $track) {
    $progmetal->addTracks($track["uri"]);
}
```

Remove tracks from a playlist  
```php
$progmetal = Sonos\Network::getPlaylistByName("progmetal");

$remove = [];
foreach($progmetal->getTracks() as $position => $track) {
    if($track["artist"] == "protest the hero") {
      $remove[] = $position;
    }
}
if(count($remove) > 0) {
    $progmetal->removeTracks($remove);
}
```
_This is done using a single call to removeTracks() for 2 reasons:_
* _1, this is more efficient as Sonos supports removing multiple tracks at once (unlike adding which must be done either by single track or album/artist/etc)_
* _2, when a track is removed all the track position indexes are recalculated, so our $position would no longer be valid_
