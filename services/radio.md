---
layout: default
title: Radio
permalink: /services/radio/
api: Services.Radio
---

Radio streaming (provided by TuneIn) is supported using the `Radio` class, which can be created like so:

```php
$radio = $sonos->getRadio();
```

From the `Radio` class you can get [Stream](../../controllers/streams/) instances for a variety of entities.

You can get your favourite stations:

```php
$stations = $radio->getFavouriteStations();
foreach ($stations as $station) {
    echo $station->getName() . "\n";
}
```

Or you favourite shows:

```php
$shows = $radio->getFavouriteShows();
foreach ($shows as $show) {
    echo $show->getName() . "\n";
}
```

You can get specific shows/stations by using their names:

```php
if ($show = $radio->getFavouriteShow("Rock Show with Daniel P Carter")) {
    $controller->useStream($show)->play();
}
$station = $radio->getFavouriteStation("Radio 1");
```
