<?php

namespace duncan3dc\Sonos\Devices;

use duncan3dc\Cache\ArrayPool;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use duncan3dc\Sonos\Interfaces\Devices\FactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\SimpleCache\CacheInterface;

final class Factory implements FactoryInterface
{
    /**
     * @var CacheInterface $cache The cache object to use for the expensive multicast discover to find Sonos devices on the network.
     */
    private $cache;

    /**
     * @var LoggerInterface $logger The logging object.
     */
    private $logger;


    /**
     * Create a new instance.
     *
     * @param CacheInterface $cache The cache object to use for the expensive multicast discover to find Sonos devices on the network
     * @param LoggerInterface $logger A logging object
     */
    public function __construct(CacheInterface $cache = null, LoggerInterface $logger = null)
    {
        if ($cache === null) {
            $cache = new ArrayPool;
        }
        $this->cache = $cache;

        if ($logger === null) {
            $logger = new NullLogger;
        }
        $this->logger = $logger;
    }


    /**
     * Create a new device.
     *
     * @param string $ip The IP address of the device
     *
     * @return DeviceInterface
     */
    public function create($ip): DeviceInterface
    {
        return new Device($ip, $this->cache, $this->logger);
    }
}
