---
layout: default
title: Getting Started
permalink: /usage/getting-started/
api: Interfaces.NetworkInterface
---

Most actions start from the [Network object](../../setup/) which has a set of methods for getting Sonos resources from your current network.

## Speakers

You can get all of the speakers available:

```php
$speakers = $sonos->getSpeakers();
```

Or the speakers for a particular room:

```php
$speakers = $sonos->getSpeakersByRoom("Living Room");
```

[See what you can do with Speakers](../speakers/)


## Controllers

Although sometimes a Controller is synonymous with a Speaker, if you have multiple speakers grouped together then only one of them is the controller. You can only send events (play/pause/etc) to the controller of a group.

Get all of the controllers available:

``` php
$controllers = $sonos->getControllers();
```

Or the controller for a particular room:

```php
$controller = $sonos->getControllerByRoom("Kitchen");
```

Or for a previously established IP address:

```php
$controller = $sonos->getControllerByIp("192.168.0.4");
```

[See what you can do with Controllers](../../controllers/play-some-music)


## Playlists

Get all the playlists available on the network:

```php
$playlists = $sonos->getPlaylists();
```

Find a playlist by its name:

```php
$playlist = $sonos->getPlaylistByName("progmetal");
```

Get a playlist you already know the internal ID of:

```php
$playlist = $sonos->getPlaylistById("SQ:204");
```

[See what you can do with Playlists](../playlists/)


## Alarms

Get all the alarms available on the network:

```php
$alarms = $sonos->getAlarms();
```

Or if you already know the ID of the alarm:

```php
$alarm = $sonos->getAlarmById(33);
```

[See what you can do with Alarms](../alarms/)
