Changelog
=========

## 0.9.0 - UNRELEASED

### Added

* [Docs] Created a changelog!
* [Alarms] Allow alarm information to be read, and managed using the Alarm class

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

--------
