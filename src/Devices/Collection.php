<?php

namespace duncan3dc\Sonos\Devices;

use duncan3dc\Sonos\Interfaces\Devices\CollectionInterface;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use duncan3dc\Sonos\Interfaces\Devices\FactoryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use function assert;

final class Collection implements CollectionInterface
{
    /**
     * @var FactoryInterface The factory to create new devices from
     */
    private $factory;

    /**
     * @var DeviceInterface[] The devices that are in this collection.
     */
    private $devices = [];

    /**
     * @var LoggerInterface|null $logger The logging object.
     */
    private $logger;


    /**
     * Create a new instance.
     *
     * @param ?FactoryInterface $factory The factory to create new devices from
     */
    public function __construct(?FactoryInterface $factory = null)
    {
        if ($factory === null) {
            $factory = new Factory();
        }
        $this->factory = $factory;
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
        $this->logger = $logger;
        $this->factory->setLogger($logger);
        return $this;
    }


    /**
     * Get the logger object to use.
     *
     * @return LoggerInterface $logger The logging object
     */
    public function getLogger(): LoggerInterface
    {
        if ($this->logger === null) {
            $this->setLogger(new NullLogger());
        }
        assert($this->logger instanceof LoggerInterface);
        return $this->logger;
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
        # Replace any existing device with the same IP address
        foreach ($this->devices as &$val) {
            if ($val->getIp() === $device->getIp()) {
                $val = $device;
                return $this;
            }
        }

        $this->devices[] = $device;

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
        $device = $this->factory->create($address);

        return $this->addDevice($device);
    }


    /**
     * Get all of the devices in this collection.
     *
     * @return DeviceInterface[]
     */
    public function getDevices(): array
    {
        return $this->devices;
    }


    /**
     * Remove all devices from this collection.
     *
     * @return $this
     */
    public function clear(): CollectionInterface
    {
        $this->devices = [];

        return $this;
    }
}
