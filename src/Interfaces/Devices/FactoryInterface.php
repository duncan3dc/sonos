<?php

namespace duncan3dc\Sonos\Interfaces\Devices;

interface FactoryInterface
{
    /**
     * Create a new device.
     *
     * @param string $ip The IP address of the device
     *
     * @return DeviceInterface
     */
    public function create($ip): DeviceInterface;
}
