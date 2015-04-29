---
layout: default
title: Logging
permalink: /logging/
---

You can trace what is happening and log any errors using a [PSR-3](http://www.php-fig.org/psr/psr-3/) compatible logger.

<p class="message-info">This feature was added in v1.1.0</p>

The logger can be passed in the constructor, or attached at any point using the `setLogger()` method:

~~~php
 # Create a logger to stdout so we can see in the terminal what's going on
 $logger = new \Monolog\Logger("sonos");
 $logger->pushHandler(new \Monolog\Handler\StreamHandler("php://stdout", \Monolog\Logger::DEBUG));

 $sonos = new Network(new \Doctrine\Common\Cache\ArrayCache, $logger);

 $sonos->setLogger($logger);

~~~


You can also get the logger back later if you need to, by default if you don't pass in a logger this will be a `NullLogger` instance that ships with `psr/log`:

~~~php
 $logger = $sonos->getLogger();
~~~


This will provide logging on any actions that are run from the `Network` class, but if you are manually creating `Speaker` instances then you'll need to pass the logger to them manually:

~~~php
 $sonos = new Network;
 $sonos->setLogger($logger);

 # This will log
 $speaker = $sonos->getSpeakerByRoom("Living Room");

 # This won't log
 $speaker = new Speaker("192.168.0.87");

 # This will log
 $speaker = new Speaker("192.168.0.87", $logger);
~~~
