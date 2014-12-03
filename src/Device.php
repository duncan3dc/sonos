<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;

/**
 * Make http requests to a Sonos device.
 */
class Device
{
    /**
     * @var string $ip The IP address of the speaker.
     */
    public $ip;

    /**
     * @var array $cache Cached data to increase performance.
     */
    protected $cache = [];


    /**
     * Create an instance of the Device class.
     *
     * @param string $ip The ip address that the speaker is listening on
     */
    public function __construct($ip)
    {
        $this->ip = $ip;
    }


    /**
     * Retrieve some xml from the speaker.
     *
     * @param string $url The url to retrieve
     *
     * @return XmlParser
     */
    public function getXml($url)
    {
        if (!isset($this->cache[$url])) {
            $this->cache[$url] = new XmlParser("http://{$this->ip}:1400{$url}");
        }

        return $this->cache[$url];
    }


    /**
     * Send a soap request to the speaker.
     *
     * @param string $service The service to send the request to
     * @param string $action The action to call
     * @param array $params The parameters to pass
     *
     * @return mixed
     */
    public function soap($service, $action, $params = [])
    {
        switch ($service) {
            case "AVTransport";
            case "RenderingControl":
                $path = "MediaRenderer";
                break;
            case "ContentDirectory":
                $path = "MediaServer";
                break;
            case "AlarmClock":
                $path = null;
                break;
            default:
                throw new \InvalidArgumentException("Unknown service: {$service}");
        }

        $location = "http://{$this->ip}:1400/";
        if ($path) {
            $location .= "{$path}/";
        }
        $location .= "{$service}/Control";

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
            return $soap->__soapCall($action, $soapParams);
        } catch (\SoapFault $e) {
            throw new Exceptions\SoapException($e, $soap);
        }
    }
}
