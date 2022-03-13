<?php

namespace duncan3dc\Sonos\Devices;

use duncan3dc\Sonos\Interfaces\Devices\CollectionInterface;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Cache the collection of devices.
 */
final class CachedCollection implements CollectionInterface
{
    private const CACHE_KEY = "device-ip-addresses";

    /**
     * @var bool $retrieved A flag to indicate whether we've retrieved the devices from cache yet or not.
     */
    private $retrieved = false;

    /**
     * @var CollectionInterface $collection The device collection to actually use.
     */
    private $collection;

    /**
     * @var CacheInterface $cache The cache object to use for finding Sonos devices on the network.
     */
    private $cache;


    /**
     * Create a new instance.
     *
     * @param CollectionInterface $collection The device collection to actually use
     * @param CacheInterface $cache The cache object to use
     */
    public function __construct(CollectionInterface $collection, CacheInterface $cache)
    {
        $this->collection = $collection;
        $this->cache = $cache;
    }


    /**
     * Set the logger object to use.
     *
     * @var LoggerInterface $logger The logging object
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->collection->setLogger($logger);

        return $this;
    }


    /**
     * Get the logger object to use.
     *
     * @return LoggerInterface $logger The logging object
     */
    public function getLogger(): LoggerInterface
    {
        return $this->collection->getLogger();
    }


    /**
     * Remove any cached data we have.
     *
     * @return void
     */
    private function clearCache()
    {
        $this->cache->delete(self::CACHE_KEY);

        $this->retrieved = false;
    }


    /**
     * Add a device to this collection.
     *
     * @param DeviceInterface $device The device to add
     *
     * @return $this
     */
    public function addDevice(DeviceInterface $device): CollectionInterface
    {
        $this->collection->addDevice($device);

        $this->clearCache();

        return $this;
    }


    /**
     * Add a device to this collection using its IP address
     *
     * @param string $address The IP address of the device to add
     *
     * @return $this
     */
    public function addIp(string $address): CollectionInterface
    {
        $this->collection->addIp($address);

        $this->clearCache();

        return $this;
    }


    /**
     * Get all of the devices on the current network
     *
     * @return DeviceInterface[]
     */
    public function getDevices(): array
    {
        # If we've already retrieved the devices from cache then just return them
        if ($this->retrieved) {
            return $this->collection->getDevices();
        }

        # If we haven't cached the available addresses yet then do it now
        if (!$this->cache->has(self::CACHE_KEY)) {
            $addresses = [];
            foreach ($this->collection->getDevices() as $device) {
                $addresses[] = $device->getIp();
            }
            $this->cache->set(self::CACHE_KEY, $addresses);
        }

        $addresses = $this->cache->get(self::CACHE_KEY);
        foreach ($addresses as $address) {
            $this->collection->addIp($address);
        }
        $this->retrieved = true;

        return $this->collection->getDevices();
    }


    /**
     * Remove all devices from this collection.
     *
     * @return $this
     */
    public function clear(): CollectionInterface
    {
        $this->collection->clear();

        $this->clearCache();

        return $this;
    }
}
