---
layout: default
title: Play Some Music
page-title: Start the party!
permalink: /controllers/play-some-music/
api: Interfaces.ControllerInterface
---


You can start your whole network playing music:

```php
$controllers = $sonos->getControllers();
foreach ($controllers as $controller) {
    if ($controller->getState() !== Controller::STATE_PLAYING) {
        $controller->play();
    }
}
```


Or check on your networks current status:

```php
$controllers = $sonos->getControllers();
foreach ($controllers as $controller) {
    echo "{$controller->room} = " . $controller->getStateName() . "\n";
}
```


As well as a bunch of other stuff:

```php
# I'm bored of this track
$controller->next();

# Oh wait, that was my favourite song
$controller->previous();

# Shush a minute
$controller->pause();
```


You can position to specific tracks, or a specific part of a track:

```php
# Start at the beginning of the queue
$controller->selectTrack(0);

# I love this bit
$controller->seek(Time::inSeconds(55));

# 3 minutes and 15 seconds
$controller->seek(Time::parse("3:15"));
```

See the <a href='../../usage/tracks/'>Tracks documentation</a> for info on adding tracks to your controller's queue.
