<?php

namespace duncan3dc\Sonos\Interfaces\Devices;

use duncan3dc\Sonos\Utils\SoapResponse;

interface DeviceInterface
{
    /**
     * Get the IP address of this device.
     *
     * @return string
     */
    public function getIp();


    public function getName(): string;


    public function getRoom(): string;


    public function getModel(): string;


    public function getUuid(): string;


    /**
     * Send a soap request to the device.
     *
     * @param string $service The service to send the request to
     * @param string $action The action to call
     * @param array<string, string|int|bool> $params The parameters to pass
     */
    public function soap(string $service, string $action, array $params = []): SoapResponse;
}
