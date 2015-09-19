---
layout: default
title: Export/Restore
permalink: /fun/export-restore/
api: ControllerState
---

The `ControllerState` class can be used to store the current state of a controller.

_It is serializable via the standard php function to allow it to be stashed somewhere._

An instance should be created from the `Controller` object:

~~~php
$state = $controller->exportState();
~~~


If you intend to export the state and stop playback,
then to avoid a gap between when the state is captured and playback is stopped you can have the `exportState()` method stop playback:

~~~php
# Export state and pause the controller
$state = $controller->exportState(true);
~~~


After exporting the state, you can apply it to any `Controller` instance like so:

~~~php
$controller->restoreState($state);
~~~
