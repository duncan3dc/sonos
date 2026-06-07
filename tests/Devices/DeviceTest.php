<?php

namespace duncan3dc\SonosTests\Devices;

use duncan3dc\Sonos\Devices\Device;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

final class DeviceTest extends TestCase
{
    private Device $device;

    private CacheInterface&MockInterface $cache;

    private LoggerInterface&MockInterface $logger;

    private ClientInterface&MockInterface $client;


    protected function setUp(): void
    {
        $this->cache = Mockery::mock(CacheInterface::class);
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->client = Mockery::mock(ClientInterface::class);
        $this->device = new Device("192.168.74.80", $this->cache, $this->logger, $this->client);
    }


    protected function tearDown(): void
    {
        Mockery::close();
    }


    public function testGetIp1(): void
    {
        $result = $this->device->getIp();
        $this->assertSame("192.168.74.80", $result);
    }


    /**
     * Ensure that the XML is read from cache and not requested if available.
     */
    public function testLookupDeviceDescription1(): void
    {
        $xml = <<<XML
            <device>
                <friendlyName>I am from cache</friendlyName>
                <modelNumber>S12</modelNumber>
                <UDN>uuid:RINCON_5CAAFD043C5801400</UDN>
                <roomName>Bedroom</roomName>
            </device>
        XML;

        $this->cache->shouldReceive("has")->with("get_xml_192.168.74.80")->once()->andReturn(true);
        $this->logger->shouldReceive("info")->with("getting xml from cache: http://192.168.74.80:1400/xml/device_description.xml")->once();
        $this->cache->shouldReceive("get")->with("get_xml_192.168.74.80")->once()->andReturn($xml);
        $this->logger->shouldReceive("debug")->with("192.168.74.80 model: S12")->once();

        $result = $this->device->getName();
        $this->assertSame("I am from cache", $result);

        # Ensure a second request doesn't even hit the cache, but is served from the instance properties
        $this->device->getName();
    }

    public function setupDeviceDescription(): void
    {
        $xml = <<<XML
            <device>
                <friendlyName>192.168.74.80 - Sonos Play:1 - RINCON_5CAAFD043C5801400</friendlyName>
                <modelNumber>S12</modelNumber>
                <UDN>uuid:RINCON_5CAAFD043C5801400</UDN>
                <roomName>Bedroom</roomName>
            </device>
        XML;

        $this->cache->shouldReceive("has")->with("get_xml_192.168.74.80")->once()->andReturn(false);
        $this->logger->shouldReceive("notice")->with("requesting xml from: http://192.168.74.80:1400/xml/device_description.xml")->once();
        $this->client->shouldReceive("request")->with("GET", "http://192.168.74.80:1400/xml/device_description.xml")->once()->andReturn(new Response(200, [], $xml));
        $this->cache->shouldReceive("set")->with("get_xml_192.168.74.80", $xml, Mockery::type(\DateInterval::class))->once();
        $this->logger->shouldReceive("debug")->with("192.168.74.80 model: S12")->once();
    }


    public function testGetName1(): void
    {
        $this->setupDeviceDescription();
        $result = $this->device->getName();
        $this->assertSame("192.168.74.80 - Sonos Play:1 - RINCON_5CAAFD043C5801400", $result);
    }


    public function testGetRoom1(): void
    {
        $this->setupDeviceDescription();
        $result = $this->device->getRoom();
        $this->assertSame("Bedroom", $result);
    }


    public function testGetModel1(): void
    {
        $this->setupDeviceDescription();
        $result = $this->device->getModel();
        $this->assertSame("S12", $result);
    }


    /**
     * Ensure an empty model number returns our default "UNKNOWN" string.
     */
    public function testGetModel2(): void
    {
        $xml = <<<XML
            <device>
                <friendlyName>192.168.74.80 - Sonos Play:1 - RINCON_5CAAFD043C5801400</friendlyName>
                <modelNumber></modelNumber>
                <UDN>uuid:RINCON_5CAAFD043C5801400</UDN>
                <roomName>Bedroom</roomName>
            </device>
        XML;

        $this->cache->shouldReceive("has")->with("get_xml_192.168.74.80")->once()->andReturn(false);
        $this->logger->shouldReceive("notice")->with("requesting xml from: http://192.168.74.80:1400/xml/device_description.xml")->once();
        $this->client->shouldReceive("request")->with("GET", "http://192.168.74.80:1400/xml/device_description.xml")->once()->andReturn(new Response(200, [], $xml));
        $this->cache->shouldReceive("set")->with("get_xml_192.168.74.80", $xml, Mockery::type(\DateInterval::class))->once();
        $this->logger->shouldReceive("debug")->with("192.168.74.80 model: UNKNOWN")->once();

        $result = $this->device->getModel();
        $this->assertSame("UNKNOWN", $result);
    }


    /**
     * Ensure a missing model number tag returns our default "UNKNOWN" string.
     */
    public function testGetModel3(): void
    {
        $xml = <<<XML
            <device>
                <friendlyName>192.168.74.80 - Sonos Play:1 - RINCON_5CAAFD043C5801400</friendlyName>
                <UDN>uuid:RINCON_5CAAFD043C5801400</UDN>
                <roomName>Bedroom</roomName>
            </device>
        XML;

        $this->cache->shouldReceive("has")->with("get_xml_192.168.74.80")->once()->andReturn(false);
        $this->logger->shouldReceive("notice")->with("requesting xml from: http://192.168.74.80:1400/xml/device_description.xml")->once();
        $this->client->shouldReceive("request")->with("GET", "http://192.168.74.80:1400/xml/device_description.xml")->once()->andReturn(new Response(200, [], $xml));
        $this->cache->shouldReceive("set")->with("get_xml_192.168.74.80", $xml, Mockery::type(\DateInterval::class))->once();
        $this->logger->shouldReceive("debug")->with("192.168.74.80 model: UNKNOWN")->once();

        $result = $this->device->getModel();
        $this->assertSame("UNKNOWN", $result);
    }


    public function testGetUuid1(): void
    {
        $this->setupDeviceDescription();
        $result = $this->device->getUuid();
        $this->assertSame("RINCON_5CAAFD043C5801400", $result);
    }
}
