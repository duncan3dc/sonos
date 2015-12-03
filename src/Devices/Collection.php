<?php

namespace duncan3dc\Sonos\Devices;

use duncan3dc\Log\LoggerAwareTrait;
use duncan3dc\Sonos\Interfaces\Devices\CollectionInterface;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use duncan3dc\Sonos\Interfaces\Devices\FactoryInterface;

final class Collection implements CollectionInterface
{
    use LoggerAwareTrait;

    /**
     * @var FactoryInterface The factory to create new devices from
     */
    private $factory;

    /**
     * @var DeviceInterface[] The devices that are in this collection.
     */
    private $devices = [];


    /**
     * Create a new instance.
     *
     * @param FactoryInterface $factory The factory to create new devices from
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
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
