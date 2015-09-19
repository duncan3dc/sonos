---
layout: default
title: Streams
permalink: /controllers/streams/
api: Tracks.Stream
---

When a controller is not using a [Queue](../queue/) it is often using a stream instead.  
You can check if a controller is currently streaming like so:

~~~php
if ($controller->isStreaming()) {
    # Streaming
}
~~~


To start controller playing a stream you have to pass an instance of the Stream class:

~~~php
$stream = new Stream("x-sonosapi-stream:s200662?sid=254&flags=8224&sn=0");

$controller->useStream($stream)->play();
~~~


`Stream` instances can also have a name, which can be retrieved using the `getName()` method:

~~~php
foreach ($sonos->getRadioStations() as $station) {
    echo $station->getName() . "\n";
}
~~~
