<?php

use duncan3dc\DomParser\XmlParser;

require __DIR__ . "/../../vendor/autoload.php";

$path = __DIR__ . $_SERVER["REQUEST_URI"];

if (file_exists($path)) {
    echo file_get_contents($path);
    return;
}

if (file_exists("{$path}.php")) {
    require "{$path}.php";

    $xml = file_get_contents("php://input");
    $parser = new XmlParser($xml);

    $tag = $parser->getTag("Envelope")->getTag("Body")->childNodes[0];
    list($ns, $action) = explode(":", $tag->tagName);
    $options = simplexml_load_string($tag->output());

    $action($options);
    return;
}

return false;
