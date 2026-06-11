---
layout: default
title: Tidal
permalink: /services/tidal/
api: Tracks-Tidal
---

Tidal tracks are handled by the `Tidal` class, which can be created like so:

```php
use duncan3dc\Sonos\Tracks\Tidal;

$track = Tidal::fromId(140040296, 2);
```

The second parameter is the account number, this is specific to your Sonos setup, if you don't provide it then it'll default to 1, but you might need to figure out what your specific one is.
The easiest way to do that is to queue up something using the official Sonos app, and then inspect your queue:

```php
foreach ($sonos->getControllerByRoom("Office")->getQueue()->getTracks() as $track) {
    echo $track->getUri() . "\n";
}
```

This will return something like `x-sonos-http:track%2f521140338.flac?sid=174&flags=24616&sn=2` and the `sn=` is the account number you need to use.
