sonos
=====

PHP classes to control Sonos speakers

Inspired by [DjMomo/sonos](https://github.com/DjMomo/sonos) and [phil-lavin/sonos](https://github.com/phil-lavin/sonos)


Classes
-------
Three classes are available:
* Network - Provides static methods to locate speakers/controllers on the current network
* Speaker - Provides a read-only interface to individual speakers
* Controller - Allows interactive with the groups of speakers. Although sometimes a Controller is synonymous with a Speaker, when speakers are grouped together only the coordinator can receive events (play/pause/etc)


Network Class
-------------
All of these methods are static
* getSpeakers() - Returns an array of Speaker instances for all speakers on the network
* getSpeakersByRoom(string $room) - Returns an array of Speaker instances for all speakers with the specified room name
* getSpeakerByRoom(string $room) - Returns a Speaker instance for the first speaker with the specified room name
* getControllers() - Returns an array of Controller instances, one instance per group of speakers
* getControllerByRoom(string $room) - Returns a Controller instance for the speaker assigned as coordinator of the specified room name


Speaker Class
-------------
All of these properties are public
* ip - The IP address of the speaker
* name - The "Friendly" name reported by the speaker
* room - The room name assigned to this speaker
There is also a public method to check if this speaker is the coordinator of it's current group
* isCoordinator() - Returns true if it is the coordinator, or false otherwise


Controller Class
----------------
The Controller class extends the Speaker class, so all the public properties/methods listed above are available, in addition to the following public methods
* getState() - Returns the current state of the group of speakers: PLAYING, PAUSED_PLAYBACK, etc
* play() - Start playing the active music for this group
* pause() - Pause the group
* next() - Skip to the next track in the current queue
* previous() - Skip back to the previous track in the current queue


Examples
--------

Get all the speakers on the network
```
$speakers = \Sonos\Network::getSpeakers();
foreach($speakers as $speaker) {
    echo $speaker->ip . "\n";
    echo "\t" . $speaker->name . " (" . $speaker->room . ")\n";
}
```

Start all groups playing music
```
$controllers = \Sonos\Network::getControllers();
foreach($controllers as $controller) {
    echo $controller->name . " (" . $controller->room . ")\n";
    echo "\tState: " . $controller->getState() . "\n";
    $controller->play();
}
```

Control what is currently playing in the Living Room, even if it is not the coordinator of it's current group
```
$controller = \Sonos\Network::getControllerByRoom("Living Room");
echo $controller->room . "\n";
$controller->pause();
```
