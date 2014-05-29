sonos
=====

A PHP class to control Sonos speakers

Inspired by [DjMomo/sonos](https://github.com/DjMomo/sonos) and [phil-lavin/sonos](https://github.com/phil-lavin/sonos)


Static Methods
--------------
* getSpeakers() - Returns an array of Controller instances for all speakers on the network


Public Properties
-----------------
* ip - The IP address of the speaker
* name - The "Friendly" name reported by the speaker
* room - The room name assigned to this speaker


Examples
--------

```
$speakers = \Sonos\Controller::getSpeakers();
foreach($speakers as $sonos) {
	echo $sonos->ip . "\n";
	echo "\t" . $sonos->name . " (" . $sonos->room . ")\n";
}
```
