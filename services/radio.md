---
layout: default
title: Radio
permalink: /services/radio/
api: Services.Radio
---

<p class="message-info">This feature was added in v1.3.0</p>

Radio streaming (provided by TuneIn) is supported using the `Radio` class, the funcionality is available on the `Network` class using the following methods...

You can get your favourite stations:

~~~php
$stations = $sonos->getRadioStations();
foreach ($stations as $station) {
    $controller->useStream($station)->play();
    break;
}
~~~

Or you favourite shows:

~~~php
$shows = $sonos->getRadioShows();
foreach ($shows as $show) {
    $controller->useStream($show)->play();
    break;
}
~~~

Each of the above methods returns an array of [Stream](../../controllers/streams/) instances.
