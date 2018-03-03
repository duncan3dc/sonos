#!/usr/bin/env php
<?php

use duncan3dc\Sonos\Network;
use GuzzleHttp\Client;
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

$port = rand(9000, 9999);
$timeout = 3600;

$ip = $sonos->getControllerByRoom("Office")->getIp();

$url = "/MediaServer/ContentDirectory/Event";
$uri = "http://{$ip}:1400{$url}";

$client = new Client;
$response = $client->request("SUBSCRIBE", $uri, [
    "headers"   =>  [
        "CALLBACK"  =>  "<http://" . "192.168.7.6" . ":{$port}>",
        "NT"        =>  "upnp:event",
        "TIMEOUT"   =>  "Second-3600",
    ],
]);

print_r($response->getHeaders());

$loop = \React\EventLoop\Factory::create();

$server = new \React\Http\Server(function (ServerRequestInterface $request) {
    echo $request->getBody() . "\n";
    return new Response(200, ["Content-Type" => "text/plain"], "Hello World!\n");
});

$socket = new \React\Socket\Server($port, $loop);
$server->listen($socket);

$loop->run();
# check the timeout and ensure we re-subscribe before the end of it
