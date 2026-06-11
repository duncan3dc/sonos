---
layout: default
title: Device Collections
permalink: /setup/devices/
---

The `Network` constructor can be passed a [CollectionInterface](../../api/classes/duncan3dc-Sonos-Interfaces-Devices-CollectionInterface.html).  
This collection handles the Sonos devices that are available on your network, there are two implementations that ship with the library:

```php
# Default collection that searches the local network for devices
$devices = new \duncan3dc\Sonos\Devices\Discovery();

# Basic collection that allows you to manually add devices
$devices = new \duncan3dc\Sonos\Devices\Collection();
$devices->addIp("192.168.1.4");
$devices->addIp("192.168.1.5");

# Create a network instance using your device collection
$sonos = new \duncan3dc\Sonos\Network($devices);
```

## New Sonos Speakers

Some Sonos devices can not be used by this library, because they do not support playing music.
We maintain a list of supported devices in the library, but when a new one is released there is normally a delay before support is added.
If you have one of these new devices then you can bypass the check by setting up like so:

```php
$devices = new \duncan3dc\Sonos\Devices\Discovery();
$sonos = new \duncan3dc\Sonos\Network($devices);
```

However if you also have devices that don't support playing music (as well as brand new devices that do) then you'll need to implement your own filter to avoid errors:

```php
$devices = new \duncan3dc\Sonos\Devices\Collection();
$discovery = new \duncan3dc\Sonos\Devices\Discovery();
foreach ($discovery->getDevices() as $device) {
    if ($device->getModel() === "BR100") {
        continue;
    }
    $devices->addDevice($device);
}
$sonos = new \duncan3dc\Sonos\Network($devices);
```



## SSDP Discovery

If you need to use an alternative multicast address for [SSDP](https://en.wikipedia.org/wiki/Simple_Service_Discovery_Protocol) you can do so using the following method:

```php
$devices = new \duncan3dc\Sonos\Devices\Discovery();
$devices->setMulticastAddress("239.255.255.250");
```

Or if you have multiple network interfaces you can force which one to use:

```php
$devices->setNetworkInterface("eth0");
```

_To see what interface arguments are available check the PHP documentation on [IP_MULTICAST_IF](http://php.net/manual/en/function.socket-get-option.php)_
