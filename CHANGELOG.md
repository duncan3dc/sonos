Changelog
=========

## x.y.z - UNRELEASED

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
