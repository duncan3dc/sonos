---
layout: default
title: Playing Mode
permalink: /controllers/playing-mode/
---

Details about what is playing is available via a State object, this can be retrieved and used like so:

~~~php
$track = $controller->getStateDetails();

echo "Now Playing: {$track->title} from {$track->album} by {$track->artist}\n";
echo "Running Time: {$track->position} / {$track->duration}\n";
~~~


If you are not playing tracks but are streaming then the State object should be used a little differently:

~~~php
$state = $controller->getStateDetails();

if ($state->stream) {
    echo "Currently Streaming: {$state->stream}\n";

    # Most streams do not provide extra information, so check before using
    if ($state->artist) {
        echo "Artist: {$state->artist}\n";
    }
}
~~~
<p class="message-info">For more information on the State object see the <a href='../../usage/tracks/#state-details'>Tracks documentation</a>.</p>



## Every day I'm shufflin'

In addition to what is currently playing, you can also control how it is playing:

~~~php
if (!$controller->getShuffle()) {
    $controller->setShuffle(true);
}
~~~

~~~php
if ($controller->getRepeat()) {
    $controller->setRepeat(false);
}
~~~

~~~php
if ($controller->getCrossfade()) {
    $controller->setCrossfade(false);
}
~~~
