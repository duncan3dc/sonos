<?php

use duncan3dc\Dom\Xml\Parser;

require __DIR__ . "/../../vendor/autoload.php";

$uri = $_SERVER["REQUEST_URI"];

$path = __DIR__ . $uri;
if (file_exists($path)) {
    echo file_get_contents($path);
    return;
}

$class = "Sonos" . str_replace("/", "\\", $uri);
$controller = new $class;

$xml = file_get_contents("php://input");
$parser = new Parser($xml);

$tag = $parser->getTag("Envelope")->getTag("Body")->getChildren()[0];
list($ns, $action) = explode(":", $tag->tagName);
$options = simplexml_load_string($tag->getValue());

$method = strtolower($action);
$result = $controller->$method($options);

?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
    <s:Body>
        <u:<?= $action; ?>Response xmlns:u="urn:schemas-upnp-org:service:ZoneGroupTopology:1">
            <?php
                foreach ($result as $key => $val) {
                    echo "<{$key}>{$val}</{$key}>\n";
                }
            ?>
        </u:<?= $action; ?>Response>
    </s:Body>
</s:Envelope>
