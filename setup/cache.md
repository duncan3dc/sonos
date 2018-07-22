---
layout: default
title: Cache
permalink: /setup/cache/
api: Devices.CachedCollection
---

By default the library will search your network for speakers each time your script runs.  
This search can take a couple of seconds, so you may want to use a [CachedCollection](../../api/classes/duncan3dc.Sonos.Devices.CachedCollection.html) to avoid doing the search every time.  

The caching is handled by PSR-16, you'll need to install an [implementation](https://packagist.org/providers/psr/simple-cache-implementation) using composer.

```php
# First create a device collection that auto discovers devices from the network
$collection = new Discovery();

# Get your PSR-16 implementation
$cache = new \Symfony\Component\Cache\Simple\FilesystemCache;

# Create a cached collection that wraps our auto discovery one.
$collection = new CachedCollection($collection, $cache);

# Finally create your network instance using the cached collection
$sonos = new Network($collection);
```

This will speed up your scripts, but it can also cause problems if you add/remove devices from your network.  
