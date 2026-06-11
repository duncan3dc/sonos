sonos
=====

A PHP library for interacting with [Sonos](https://www.sonos.com/) speakers.  

Full documentation is available at https://duncan3dc.github.io/sonos/  
PHPDoc API documentation is also available at [https://duncan3dc.github.io/sonos/api/](https://duncan3dc.github.io/sonos/api/packages/Application.html)  

[![release](https://poser.pugx.org/duncan3dc/sonos/version.svg)](https://packagist.org/packages/duncan3dc/sonos)
[![build](https://github.com/duncan3dc/sonos/actions/workflows/buildcheck.yml/badge.svg)](https://github.com/duncan3dc/sonos/actions/workflows/buildcheck.yml?query=branch%3Amain)
[![coverage](https://codecov.io/gh/duncan3dc/sonos/graph/badge.svg)](https://codecov.io/gh/duncan3dc/sonos)


Getting Started
--------------

```php
$sonos = new \duncan3dc\Sonos\Network();
$controllers = $sonos->getControllers();
foreach ($controllers as $controller) {
    echo $controller->getRoom() . " (" . $controller->getStateName() . ")\n";
}
```

_Read more at https://duncan3dc.github.io/sonos/_  


Changelog
---------
A [Changelog](CHANGELOG.md) has been available since version 0.8.8  
This library was based on the great work done by both [DjMomo/sonos](https://github.com/DjMomo/sonos) and [phil-lavin/sonos](https://github.com/phil-lavin/sonos)


Where to get help
-----------------
Found a bug? Got a question? Just not sure how something works?  
Please [create an issue](https://github.com/duncan3dc/sonos/issues) and I'll do my best to help out.  
Alternatively you can connect with me on [LinkedIn](https://linkedin.com/in/duncan3dc)
