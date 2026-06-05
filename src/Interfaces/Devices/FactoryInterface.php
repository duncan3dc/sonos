<?php

namespace duncan3dc\Sonos\Interfaces\Devices;

use Psr\Log\LoggerAwareInterface;

interface FactoryInterface extends LoggerAwareInterface
{
    /**
     * Create a new device.
     *
     * @param string $ip The IP address of the device
     */
    public function create(string $ip): DeviceInterface;
}
