#!/usr/bin/env php
<?php

use duncan3dc\Sonos\Network;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;

require __DIR__ . "/vendor/autoload.php";

$cache = new \duncan3dc\Cache\FilesystemPool("/tmp/sonos");
$collection = new \duncan3dc\Sonos\Devices\CachedCollection(new \duncan3dc\Sonos\Devices\Collection(new \duncan3dc\Sonos\Devices\Factory), $cache);
if (count($collection->getDevices()) < 1) {
    $lines = [];
    exec("ssh kessel 'gssdp-discover --interface=wlp1s0 --timeout=3 --target=urn:schemas-upnp-org:device:ZonePlayer:1 --message-type=available'", $lines);
    foreach ($lines as $line) {
        if (preg_match("/Location: http:\/\/([0-9\.]+):1400/", $line, $matches)) {
            $collection->addIp($matches[1]);
        }
    }
}
$sonos = new Network($collection);

$loop = \React\EventLoop\Factory::create();

$server = new \React\Http\Server(function (ServerRequestInterface $request) {
    $xml = new \duncan3dc\DomParser\XmlParser((string) $request->getBody());
    echo $xml->format(true) . "\n";
    return new Response;
});

$port = rand(9000, 9999);
$host = "192.168.7.6:{$port}";

$socket = new \React\Socket\Server($host, $loop);
$server->listen($socket);
# check the timeout and ensure we re-subscribe before the end of it

$loop->addTimer(2, function () use ($sonos, $host) {
    $speaker = $sonos->getSpeakerByRoom("Office");
    $speaker->subscribe($host);
});

$loop->run();
