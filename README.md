sonos
=====

PHP classes to control Sonos speakers

[![Build Status](https://travis-ci.org/duncan3dc/sonos.svg?branch=master)](https://travis-ci.org/duncan3dc/sonos)
[![Latest Stable Version](https://poser.pugx.org/duncan3dc/sonos/version.svg)](https://packagist.org/packages/duncan3dc/sonos)

Inspired by [DjMomo/sonos](https://github.com/DjMomo/sonos) and [phil-lavin/sonos](https://github.com/phil-lavin/sonos)


Classes
-------
Three classes are available:  
* Network - Provides static methods to locate speakers/controllers on the current network  
* Speaker - Provides an interface to individual speakers that is mostly read-only, although the volume can be set using this class  
* Controller - Allows interaction with the groups of speakers. Although sometimes a Controller is synonymous with a Speaker, when speakers are grouped together only the coordinator can receive events (play/pause/etc)  
* Queue - Provides an interface for managing the queue of a controller  
* Playlist - Provides an interface for managing Sonos playlists on the current network (extends the Queue class)  


Network Class
-------------
Public static properties  
* $cache: boolean - Setting this to true will cache the expensive multicast discover to find sonos devices on the network  
Public static methods  
* getSpeakers(): array - Returns an array of Speaker instances for all speakers on the network  
* getSpeaker(): Speaker - Returns a Speaker instance for the first speaker found on the network  
* getSpeakersByRoom(string $room): array - Returns an array of Speaker instances for all speakers with the specified room name  
* getSpeakerByRoom(string $room): Speaker - Returns a Speaker instance for the first speaker with the specified room name  
* getControllers(): array - Returns an array of Controller instances, one instance per group of speakers  
* getControllerByRoom(string $room): Controller - Returns a Controller instance for the speaker assigned as coordinator of the specified room name  
* getPlaylists(): array - Returns an array of Playlist instances  
* getPlaylistByName($name): Playlist - Returns a Playlist instance for the playlist with the specified name. If no case-sensitive match is found it will return a case-insensitive match, or throw an Exception if no match is found  


Speaker Class
-------------
All of these properties are public  
* ip: string - The IP address of the speaker  
* name: string - The "Friendly" name reported by the speaker  
* room: string - The room name assigned to this speaker  
There are also the folllwing public methods  
* isCoordinator(): boolean - Returns true if this speaker is the coordinator of it's current group  
* getVolume(): int - Get the current volume of this speaker as an integer between 0 and 100  
* setVolume(int $volume): null - Set the current volume of this speaker  
* adjustVolme(int $adjust): null - Adjust the volume of this speaker by a relative amount between -100 and 100  


Controller Class
----------------
The Controller class extends the Speaker class, so all the public properties/methods listed above are available, in addition to the following public methods  
* getState(): int - Returns the current state of the group of speakers using the Controller class constants:  
  STATE_STOPPED  
  STATE_PLAYING  
  STATE_PAUSED  
  STATE_TRANSITIONING  
  STATE_UNKNOWN  
* getStateName(): string - Returns the current state of the group of speakers as the string reported by sonos: PLAYING, PAUSED_PLAYBACK, etc  
* getStateDetails(): array - Returns an array of attributes about the currently active track in the queue  
* play(): null - Start playing the active music for this group  
* pause(): null - Pause the group  
* next(): null - Skip to the next track in the current queue  
* previous(): null - Skip back to the previous track in the current queue  
* getSpeakers(): array - Returns an array of Speaker instances that are in the group of this Controller  
* addSpeaker(Speaker $speaker): null - Adds the specified speaker to the group of this Controller  
* removeSpeaker(Speaker $speaker): null - Removes the specified speaker from the group of this Controller  
* setVolume(int $volume): null - Set the current volume of all the speakers controlled by this Controller  
* adjustVolme(int $adjust): null - Adjust the volume of all the speakers controlled by this Controller by a relative amount between -100 and 100  
* getMode(): array - Get the current play mode settings, the array contains 2 boolean elements (shuffle & repeat)  
* setMode(array $options): null - Set the current play mode settings, using the array returned by getMode()  
* getRepeat(): boolean - Check if repeat is currently active  
* setRepeat(boolean $repeat): null - Turn repeat mode on or off  
* getShuffle(): boolean - Check if shuffle is currently active  
* setShuffle(boolean $shuffle): null - Turn shuffle mode on or off  
* getQueue(): Queue - Returns a Queue instance representing the queue for this controller  


Queue Class
-----------
Public methods  
* getTracks([int $start, int $limit]): array - Returns an array of tracks on the queue, with optional start position (zero-based) and total tracks to return  
* addTracks(mixed $tracks [, int $position]): boolean - Add a track to the queue, optionally specifying the position, by default it will add the track to the end. Tracks are specified using the URI, and multiple tracks can be added by passing $tracks as an array of URIs  
* removeTracks(mixed $positions): boolean - Remove tracks from the queue by their zero-based position. A single track can be removed or multiple by passing an array of positions  


Playlist Class
-------------
As this class extends the Queue class all of the public methods above are available, along with the following  
* getId(): string - Returns the id of the playlist  
* getName(): string - Returns the name of the playlist  
* delete(): boolean - Deletes this playlist entirely  
There is also a static method for creating new playlists  
* create(string $name): Playlist - Creates a new playlist with the specified name and returns a Playlist instance  


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
