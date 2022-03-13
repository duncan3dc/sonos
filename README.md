sonos
=====

A PHP library for interacting with [Sonos](http://www.sonos.com/) speakers.  

Full documentation is available at http://duncan3dc.github.io/sonos/  
PHPDoc API documentation is also available at [http://duncan3dc.github.io/sonos/api/](http://duncan3dc.github.io/sonos/api/namespaces/duncan3dc.Sonos.html)  

[![release](https://poser.pugx.org/duncan3dc/sonos/version.svg)](https://packagist.org/packages/duncan3dc/sonos)
[![build](https://github.com/duncan3dc/sonos/workflows/.github/workflows/buildcheck.yml/badge.svg?branch=main)](https://github.com/duncan3dc/sonos/actions?query=branch%3Amain+workflow%3A.github%2Fworkflows%2Fbuildcheck.yml)
[![coverage](https://codecov.io/gh/duncan3dc/sonos/graph/badge.svg)](https://codecov.io/gh/duncan3dc/sonos)


Quick Examples
--------------

Start all groups playing music
```php
$sonos = new \duncan3dc\Sonos\Network;
$controllers = $sonos->getControllers();
foreach ($controllers as $controller) {
    echo $controller->name . " (" . $controller->room . ")\n";
    echo "\tState: " . $controller->getState() . "\n";
    $controller->play();
}
```

Add all the tracks from one playlist to another
```php
$sonos = new \duncan3dc\Sonos\Network;
$protest = $sonos->getPlaylistByName("protest the hero");
$progmetal = $sonos->getPlaylistByName("progmetal");

foreach ($protest->getTracks() as $track) {
    $progmetal->addTracks($track["uri"]);
}
```

_Read more at http://duncan3dc.github.io/sonos/_  


Changelog
---------
A [Changelog](CHANGELOG.md) has been available since version 0.8.8


Where to get help
-----------------
Found a bug? Got a question? Just not sure how something works?  
Please [create an issue](//github.com/duncan3dc/sonos/issues) and I'll do my best to help out.  
Alternatively you can catch me on [Twitter](https://twitter.com/duncan3dc)


## duncan3dc/sonos for enterprise

Available as part of the Tidelift Subscription

The maintainers of duncan3dc/sonos and thousands of other packages are working with Tidelift to deliver commercial support and maintenance for the open source dependencies you use to build your applications. Save time, reduce risk, and improve code health, while paying the maintainers of the exact dependencies you use. [Learn more.](https://tidelift.com/subscription/pkg/packagist-duncan3dc-sonos?utm_source=packagist-duncan3dc-sonos&utm_medium=referral&utm_campaign=readme)
