#!/usr/local/bin/php
<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\Helpers\Helper;
use Psr\Http\Message\ServerRequestInterface;

require __DIR__ . "/vendor/autoload.php";

$sonos = new Network;

$ip = "192.168.7.6";
$port = 10505;

$controllers = [];
foreach ($sonos->getControllers() as $controller) {
    # Subscribe to events for this controller
    $data = Helper::curl([
        "url"       =>  "http://" . $controller->getIp() . ":1400/MediaRenderer/AVTransport/Event",
        "custom"    =>  "SUBSCRIBE",
        "headers"   =>  [
            "CALLBACK"  =>  "<http://{$ip}:{$port}>",
            "NT"        =>  "upnp:event",
            "TIMEOUT"   =>  "Second-600",
        ],
        "returnheaders" =>  true,
    ]);
    $sid = $data["headers"]["SID"];
    $controllers[$sid] = $controller;
}

$loop = \React\EventLoop\Factory::create();

$socket = new \React\Socket\Server("{$ip}:{$port}", $loop);
$http = new \React\Http\Server($socket);
$http->on("request", function(ServerRequestInterface $request) use ($controllers) {
    if ($request->getMethod() !== "NOTIFY") {
        $response->writeHead(403);
        $response->end();
        return;
    }

    $headers = $request->getHeaders();
    $sid = $headers["SID"];
    $controller = $controllers[$sid];

    $size = $headers["CONTENT-LENGTH"];

    $xml = "";
    $request->on("data", function($data) use ($response, $controller, &$xml, $size) {
        $xml .= $data;
        echo "data (" . strlen($xml) . ") [{$size}]\n";
        if (strlen($xml) < $size) {
            return;
        }

        $xml = (new XmlParser($xml))->getTag("LastChange")->nodeValue;
        $event = (new XmlParser($xml))->getTag("InstanceID");
        echo $controller->room . "\n";
        echo $event->output() . "\n";
        echo "-------------------------------------------------------------------------------------------------\n";

        $response->writeHead(200);
        $response->end();
    });
});
$http->listen($socket);

$stdin = new \React\Stream\Stream(fopen("php://stdin", "r"), $loop);
$stdin->on("data", function($data) {
    echo $data . "\n";
});

$loop->run();
