<?php

namespace duncan3dc\SonosTests;

use duncan3dc\Sonos\Network;

class NetworkTest extends \PHPUnit_Framework_TestCase
{
    protected $network;

    public function setUp()
    {
        $this->network = new Network;
    }


    protected function getCacheKey()
    {
        $class = new \ReflectionClass($this->network);

        $method = $class->getMethod("getCacheKey");
        $method->setAccessible(true);

        return $method->invoke($this->network);
    }


    public function testDefaultValues()
    {
        $this->assertSame("devices_NULL__239.255.255.250", $this->getCacheKey());
    }


    public function testSetMulticastAddress()
    {
        $this->network->setMulticastAddress("127.0.0.1");
        $this->assertSame("devices_NULL__127.0.0.1", $this->getCacheKey());
    }


    public function testSetNetworkInterfaceString()
    {
        $this->network->setNetworkInterface("eth0");
        $this->assertSame("devices_string_eth0_239.255.255.250", $this->getCacheKey());
    }


    public function testSetNetworkInterfaceInteger()
    {
        $this->network->setNetworkInterface(0);
        $this->assertSame("devices_integer_0_239.255.255.250", $this->getCacheKey());
    }


    public function testSetNetworkInterfaceEmptyString()
    {
        $this->network->setNetworkInterface("");
        $this->assertSame("devices_string__239.255.255.250", $this->getCacheKey());
    }
}
