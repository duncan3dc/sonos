Changelog
=========

## x.y.z - UNRELEASED

--------

## 2.2.6 - 2024-12-18

### Changed

* [Support] Added support for PHP 8.3 and 8.4.

--------

## 2.2.5 - 2024-12-18

### Changed

* [Support] Added support for the updated PSRs.

--------

## 2.2.4 - 2023-04-30

### Changed

* [Support] Added support for Guzzle 7.

--------

## 2.2.3 - 2023-01-17

### Changed

* [Support] Added support for PHP 8.2.

--------

## 2.2.2 - 2022-09-06

### Added

* [Dependencies] Added support for a domparser 2.0.

--------

## 2.2.1 - 2022-04-19

### Fixed

* [Device] Added support for a bunch of other speaker models ([#122](https://github.com/duncan3dc/sonos/issues/122)).

--------

## 2.2.0 - 2022-04-14

### Fixed

* [Device] Added support for the Play:5 Gen2 speaker ([#117](https://github.com/duncan3dc/sonos/issues/117)).
* [Dependencies] Avoid a security issue in league/flysystem ([#121](https://github.com/duncan3dc/sonos/issues/121)).

### Changed

* [Support] Added support for PHP 8.0, and 8.1.
* [Support] Dropped support for PHP 7.1, and 7.2.

--------

## 2.1.1 - 2019-11-03

### Fixed

* [Device] Added support for the new Move speakers.

--------

## 2.1.0 - 2019-08-25

### Added

* [Alarms] Allow music to be get/set via `getMusic()` and `setMusic()`.
* [Queues] `addTrack()` now accepts `PlaylistInterface` instances for efficiency.

### Fixed

* [Device] Added support for the new SYMFONISK speakers.
* [Device] Added support for the new version of the PLAY:ONE.
* [Queues] Correct the handling of errors when adding a lot of tracks.

--------

## 2.0.7 - 2019-03-15

### Fixed

* [Device] Added support for the new CONNECT (S15) device.

--------

## 2.0.6 - 2018-12-14

### Fixed

* [Device] Resolved a cache issue causing XML errors.

--------

## 2.0.5 - 2018-12-13

### Fixed

* [Logging] Ensure network loggers are passed all the way down to new devices.
* [Controllers] Resolved several grouping/coordinator issues.

### Added

* [Support] Added support for PHP 7.3

--------

## 2.0.4 - 2018-10-24

### Fixed

* [Network] Ignore speakers that are hidden as part of a stereo pair.
* [Streams] Ensure Amazon Radio streams are recognised.

--------

## 2.0.3 - 2018-09-13

### Fixed

* [Time] Avoid confusing parse() results by using fromFormat().

--------

## 2.0.2 - 2018-07-22

### Fixed

* [Network] Added support for the BEAM devices.

--------

## 2.0.1 - 2018-07-22

### Changed

* [Collection] The collection class no longer requires a factory, it will create the default one if none is provided.
* [Discovery] The Discovey class no longer requires a collection, it will create the default one if none is provided.

--------

## 2.0.0 - 2018-07-21

### Added

* [Network] A new DeviceCollection class can be used to manually add devices to the network.
* [Time] A Time class has been added to normalise the various representations of time used by Sonos.

### Changed

* [Network] The construction of a new instance now only accepts a DeviceCollection instance.
* [Network] Replaced the doctrine/cache dependency with any PSR-16 compatible cache library.
* [Controllers] An exception is now throw when adding track(s) fails.
* [General] Scalar parameter type hints have been added where possible.
* [General] Return type hints have been added where possible.
* [General] All classes now implement an interface, and are marked as final to prevent inheritance.
* [General] All public properties are no longer visible and should be accessed/updated using their getters/setters.
* [General] NotFoundException is now thrown instead of methods returning null.
* [Controllers] The class constants are now on ControllerInterface.
* [Alarms] The class constants are now on the AlarmInterface.
* [TextToSpeech] You must pass the provider you want to use, there is no default.
* [Support] Drop support for PHP 5.6 and PHP 7.0
* [Support] Drop support for HHVM as there is little demand to make it worthwhile.

### Removed

* [Tracks] The deprecated `getTitle()` method has been removed, use `getName()` instead.
* [Radio] The deprecated `getRadioStations()` and `getRadioShows()` methods have been removed, use `getRadio()->getFavouriteShows()` and `getRadio()->getFavouriteStations()` instead.
* [Controllers] The getNetwork() method has been removed.

### Fixed

* [Controllers] Corrected the group look ups for Sonos version 9.1

--------

## 1.9.11 - 2017-11-25

### Added

* [Support] Loosen the requirement on psr/log.

--------

## 1.9.10 - 2017-11-03

### Added

* [Network] Added support for the new ONE devices.

--------

## 1.9.9 - 2017-09-29

### Added

* [Network] Added support for the PLAYBASE devices.
* [Support] Added support for PHP 7.1

### Removed

* [Support] Dropped support for HHVM

--------

## 1.9.8 - 2017-02-22

### Fixed

* [Tracks] Added a GoogleUnlimited track to support Google unlimited tracks.

--------

## 1.9.7 - 2017-02-19

### Fixed

* [Controllers] Ensure PlayBar streaming continues after using interrupt().

--------

## 1.9.6 - 2017-02-12

### Fixed

* [Tracks] Allow text-to-speech messages longer than 100 characters.

--------

## 1.9.5 - 2017-01-19

### Added

* [Logging] Soap request and responses are now logged under the Debug level.
* [Support] Added support for PHP 7.1

--------

## 1.9.4 - 2016-12-31

### Fixed

* [Network] Added support for the new version of the PLAY:1.

--------

## 1.9.3 - 2016-10-04

### Fixed

* [Streams] Ensure the title is picked up when available.
* [Queues] Prevent inifite loop when the start position is invalid.

--------

## 1.9.2 - 2016-09-12

### Added

* [Controller] Allow the Network instance in use to be retrieved using getNetwork().

### Fixed

* [Tracks] Fix the caching of text-to-speech files.

--------

## 1.9.1 - 2016-03-13

### Added

* [Network] Add support for the ZP100 device.

--------

## 1.9.0 - 2016-03-12

### Added

* [Controller] Allow the Line-In to be controlled.

### Changed

* [Controller] The isStreaming() method now returns true when streaming from Line-In.

--------

## 1.8.0 - 2016-01-10

### Added

* [Network] Allow the network interface to be specified using Network::setNetworkInterface().

### Changed

* [Network] Correct the cache lookup to only use cache from the same network interface and multicast address.

--------

## 1.7.4 - 2015-12-03

### Fixed

* [Controllers] The getStateDetails() method can now handle Line-In streams and return a valid State instance.

--------

## 1.7.3 - 2015-11-19

### Fixed

* [Radio] Correct the constants used for retrieving favourites.
* [Alarms] Fix HHVM handling of days (array constants not valid).

--------

## 1.7.2 - 2015-10-18

### Fixed

* [Alarms] Correct the handling of days (Sunday is zero and the rest were off by one).

--------

## 1.7.1 - 2015-10-16

### Fixed

* [Playlists] Correct the adding of tracks that was broken in 1.5.0.

--------

## 1.7.0 - 2015-09-19

### Added

* [Tracks] Created a Google track to handle their specific metadata.
* [Tracks] Allow the Spotify region to be overridden.

### Fixed

* [Tracks] Prevent other services being incorrectly treated as Deezer tracks.

### Changed

* [Support] Drop support for PHP 5.5, as it nears end-of-life and constant expressions require 5.6

--------

## 1.6.1 - 2015-09-16

### Fixed

* [Playlist] Ensure the TrackFactory is available when working with playlists.

--------

## 1.6.0 - 2015-09-09

### Added

* [Tracks] Created Spotify/Deezer tracks to handle their specific metadata.

### Fixed

* [Tracks] The album art now only prepends a host if it is missing one

### Removed

* [Tracks] The QueueTrack has been merged with the Track class.

--------

## 1.5.1 - 2015-09-08

### Added

* [Network] Add support for the ZP80 ZonePlayer device.

--------

## 1.5.0 - 2015-08-29

### Changed

* [Tracks] Use league/flysystem to allow access to SMB shares from other machines.
* [Queues] Improve efficiency of adding tracks by adding up to 16 tracks at once.

--------

## 1.4.2 - 2015-08-16

### Changed

* [Network] Improve the topology caching as these change fairly frequently.

--------

## 1.4.0 - 2015-06-15

### Added

* [Streams] Allow the name/title of a stream to be retrieved.

### Fixed

* [Spotify] Enable metadata (artist, album, etc) to display correctly in some cases.

### Changed

* [Network] Cache the device descriptions and topology (these rarely change so the performance improvement is preferable).
* [Support] Drop support for PHP 5.4, as it nears end-of-life and Guzzle 6 requires 5.5

--------

## 1.3.1 - 2015-05-30

### Added

* [Network] Add methods for getting radio station/show information.

--------

## 1.3.0 - 2015-05-29

### Added

* [Tracks] Created a Radio class.

### Changed

* [Tracks] Use duncan3dc/speaker for text-to-speech handling

### Fixed

* [Tracks] Correct the handling of queueid to avoid metadata loss.
* [Controllers] Only seek if we have some tracks in the queue.

--------

## 1.2.0 - 2015-04-29

### Added

* [Network] Added support for the PLAYBAR and CONNECT devices (treated as the same as PLAY:1, PLAY:3, etc).
* [Tracks] Created a Directory class to handle SMB music library shares.
* [Tracks] Created a TextToSpeech class.
* [Controllers] Added a method to interrupt playback with a single track.
* [Controllers] Created selectTrack() and seek() methods.
* [Controllers] Allowed state to be exported and restored.
* [Controllers] Added methods to check if a controller is streaming or using a queue.
* [Speakers] Added speaker LED functionality to turn on and off, and check status.
* [Speakers] Added equaliser functionality (treble, bass, loudness).

### Fixed
* [Queues] Detect and throw an understandable error when an empty queue is attempted to be played.

--------

## 1.1.0 - 2015-02-27

### Added

* [Alarms] Allow the room/speaker of an alarm to be get and set.
* [Logging] Allow a PSR-3 compatible logger to be passed for logging support.

### Fixed
* [Network] Ignore any non Sonos devices from the discovery.
* [Network] Ignore any Sonos devices that are not speakers (bridges, etc).

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
