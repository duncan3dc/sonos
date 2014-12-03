Changelog
=========

## 1.0.0 - UNRELEASED

### Added

* [Alarms] Allow alarm information to be read, and managed using the Alarm class

### Changed

* [Network] The Network class is no longer static, it should be instantiated before calling its methods.
* [Controllers] Controller::getStateDetails() nows return an instance of the State class
* [Queues/Playlists] Queue::getTracks() now returns an array of QueueTrack instances
* [Playlists] Creating playlists is now done using the createPlaylist() method on the Network class

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
