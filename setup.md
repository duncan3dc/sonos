---
layout: default
title: Setup
permalink: /setup/
api: Network
---

All classes are in the `duncan3dc\Sonos` namespace.

```php
require_once __DIR__ . "vendor/autoload.php";

use duncan3dc\Sonos\Network;

$sonos = new Network;
```


Cache
-----

By default the library will search your network for speakers each time your script runs.  
This search can take a couple of seconds, so you may want to use a [CachedCollection](cache/) to avoid doing the search every time.  


Device Collections
------------------

If you have an unusual network set up, or want more control over the devices available, [device collections](devices/) are the answer.  


