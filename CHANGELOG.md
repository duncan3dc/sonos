Changelog
=========

## x.y.z - UNRELEASED

### Fixed
* [Network] Ignore any non Sonos devices from the discovery.

--------

## 1.0.6 - 2015-01-30

### Fixed

* [Network] Return null from getControllerByRoom() if there are no speakers found for that room.

--------

## 1.0.5 - 2015-01-18

### Added

* [Dependencies] Bumped the doctrine/cache requirement to ~1.4.0

--------

## 1.0.4 - 2015-01-17

### Added

* [Playlists] Created a moveTrack() method to re-order playlist tracks.
* [Playlists] Created a hasPlaylist() method on the Network class to check if a playlist exists.

--------

## 1.0.3 - 2015-01-06

### Fixed

* [Network] Clear the internal cache of how speakers are grouped when one is removed/added.

--------

## 1.0.2 - 2015-01-05

### Fixed

* [Network] If no devices are found on the network the result is no longer cached.

--------

## 1.0.1 - 2014-12-30

### Added

* [Alarms] Allow alarm information to be read, and managed using the Alarm class
* [Controllers] Added support for Crossfade.

### Changed

* [Network] The Network class is no longer static, it should be instantiated before calling its methods.
* [Network] The cache handling is now provided by doctrine/cache
* [Controllers] The getStateDetails() method now returns an instance of the State class.
* [Playlists] Creating playlists is now done using the createPlaylist() method on the Network class.
* [Queues/Playlists] Adding individual tracks is now doing using addTrack(), and addTracks() only supports arrays.
* [Queues/Playlists] The getTracks() method now returns an array of QueueTrack instances.

--------

## 0.8.8 - 2014-12-03

### Added

* [Docs] Created a changelog!

### Changed

* [Exceptions] Methods that throw exceptions related to parameters now throw InvalidArgumentException
```
Controller::__construct()
Controller::setState()
Network::getSpeakerByRoom()
Network::getSpeakersByRoom()
Network::getControllerByRoom()
Network::getPlaylistByName()
```

### Fixed

* [Controllers] The getStateDetails() method can now handle empty queues and return a valid array.

--------
