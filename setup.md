---
layout: default
title: Setup
permalink: /setup/
---

All classes are in the `duncan3dc\Sonos` namespace.

By default the speakers found on your network are cached, if you frequently add/remove sonos devices from your network then you'll want to use a shorter lived cache, or you can clear the cache manually.

~~~php
require_once __DIR__ . "vendor/autoload.php";

use duncan3dc\Sonos\Network;

# Get a new instance of the Network class using a long lived cache
$sonos = new Network;
~~~

<p class="message-info">As of v1.0.2, if no speakers are found then this result is not cached, and the discovery will be tried again on the next request.</p>


Cache
-----

We use [doctrine/cache](http://doctrine-common.readthedocs.org/en/latest/reference/caching.html) to handle caching, which means a variety of cache backends are available.

~~~php
# Get a new instance of the Network class using array cache
$sonos = new Network(new \Doctrine\Common\Cache\ArrayCache);

# Use a custom cache instance that can be cleared on demand
$cache = new \Doctrine\Common\Cache\FilesystemCache("/tmp/sonos-cache");
$cache->deleteAll();
$sonos = new Network($cache);
~~~


Additionally, any class that implements the [doctrine cache interface](https://github.com/doctrine/cache/blob/master/lib/Doctrine/Common/Cache/Cache.php) can be used.


SSDP Discovery
--------------

If you need to use an alternative multicast address for [SSDP](https://en.wikipedia.org/wiki/Simple_Service_Discovery_Protocol) you can do so using the following method:

~~~php
$sonos->setMulticastAddress("239.255.255.250");
~~~

<p class="message-info">This method was added in v1.4.1</p>
