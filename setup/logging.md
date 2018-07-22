---
layout: default
title: Logging
permalink: /setup/logging/
---

You can trace what is happening and log any errors using a [PSR-3](//www.php-fig.org/psr/psr-3/) compatible logger.

The logger can be passed in the constructor, or attached at any point using the `setLogger()` method:

```php
# Create a logger to stdout so we can see in the terminal what's going on
$logger = new \Monolog\Logger("sonos");
$handler = new \Monolog\Handler\StreamHandler("php://stdout", \Monolog\Logger::DEBUG);
$logger->pushHandler($handler);

$sonos = new Network();
$sonos->setLogger($logger);
```


You can also get the logger back later if you need to, by default if you don't pass in a logger this will be a `NullLogger` instance that ships with `psr/log`:

```php
$logger = $sonos->getLogger();
```
