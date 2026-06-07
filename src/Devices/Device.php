<?php

namespace duncan3dc\Sonos\Devices;

use duncan3dc\Cache\ArrayPool;
use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Exceptions\InvalidArgumentException;
use duncan3dc\Sonos\Exceptions\SoapException;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;

use function preg_match;

/**
 * Make http requests to a Sonos device.
 */
final class Device implements DeviceInterface
{
    private string $ip;

    private bool $lookedUp = false;

    private string $name;

    private string $room;

    private string $model;

    private string $uuid;

    private CacheInterface $cache;

    private LoggerInterface $logger;

    private ClientInterface $client;


    public function __construct(string $ip, ?CacheInterface $cache = null, ?LoggerInterface $logger = null, ?ClientInterface $client = null)
    {
        $this->ip = $ip;

        if ($cache === null) {
            $cache = new ArrayPool();
        }
        $this->cache = $cache;

        if ($logger === null) {
            $logger = new NullLogger();
        }
        $this->logger = $logger;

        if ($client === null) {
            $client = new Client();
        }
        $this->client = $client;
    }


    public function getIp()
    {
        return $this->ip;
    }


    private function lookupDeviceDescription(): void
    {
        if ($this->lookedUp) {
            return;
        }

        $parser = $this->getXml();
        $device = $parser->getTag("device");

        $this->name = (string) $device->getTag("friendlyName");
        $this->room = (string) $device->getTag("roomName");
        $this->model = (string) $device->getTag("modelNumber") ?: "UNKNOWN";
        $this->logger->debug("{$this->ip} model: {$this->model}");

        $udn = (string) $device->getTag("UDN");
        if (preg_match("/^uuid:(.*)$/", $udn, $matches)) {
            $this->uuid = $matches[1];
        }

        $this->lookedUp = true;
    }


    /**
     * Retrieve some xml from the device.
     */
    private function getXml(): XmlParser
    {
        $uri = "http://{$this->ip}:1400/xml/device_description.xml";

        $cacheKey = "get_xml_{$this->ip}";
        if ($this->cache->has($cacheKey)) {
            $this->logger->info("getting xml from cache: {$uri}");
            $xml = $this->cache->get($cacheKey);
            if ($xml) {
                return new XmlParser($xml);
            }
            $this->logger->error("empty xml in cache");
        }

        $this->logger->notice("requesting xml from: {$uri}");
        $xml = (string) $this->client->request("GET", $uri)->getBody();

        $this->cache->set($cacheKey, $xml, new \DateInterval("P1D"));

        return new XmlParser($xml);
    }


    public function getName(): string
    {
        $this->lookupDeviceDescription();
        return $this->name;
    }


    public function getRoom(): string
    {
        $this->lookupDeviceDescription();
        return $this->room;
    }


    public function getModel(): string
    {
        $this->lookupDeviceDescription();
        return $this->model;
    }


    public function getUuid(): string
    {
        $this->lookupDeviceDescription();
        return $this->uuid;
    }


    /**
     * Send a soap request to the device.
     *
     * @param string $service The service to send the request to
     * @param string $action The action to call
     * @param array<string, string|int|bool> $params The parameters to pass
     *
     * @return mixed
     */
    public function soap(string $service, string $action, array $params = [])
    {
        switch ($service) {
            case "AVTransport":
            case "RenderingControl":
                $path = "MediaRenderer";
                break;
            case "ContentDirectory":
                $path = "MediaServer";
                break;
            case "AlarmClock":
            case "DeviceProperties":
            case "ZoneGroupTopology":
                $path = null;
                break;
            default:
                throw new InvalidArgumentException("Unknown service: {$service}");
        }

        $location = "http://{$this->ip}:1400/";
        if (is_string($path)) {
            $location .= "{$path}/";
        }
        $location .= "{$service}/Control";

        $this->logger->info("sending soap request to: {$location}", $params);

        $soap = new \SoapClient(null, [
            "location"  =>  $location,
            "uri"       =>  "urn:schemas-upnp-org:service:{$service}:1",
            "trace"     =>  true,
        ]);

        $soapParams = [];
        $params["InstanceID"] = 0;
        foreach ($params as $key => $val) {
            $soapParams[] = new \SoapParam(new \SoapVar($val, \XSD_STRING), $key);
        }

        try {
            $result = $soap->__soapCall($action, $soapParams);
            $this->logger->debug("REQUEST: " . $soap->__getLastRequest());
            $this->logger->debug("RESPONSE: " . $soap->__getLastResponse());
        } catch (\SoapFault $e) {
            $this->logger->debug("REQUEST: " . $soap->__getLastRequest());
            $this->logger->debug("RESPONSE: " . $soap->__getLastResponse());
            throw new SoapException($e, $soap);
        }

        return $result;
    }
}
