<?php

namespace duncan3dc\Sonos\Interfaces\Devices;

use duncan3dc\DomParser\XmlParser;

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
     *
     * @return mixed
     */
    public function soap(string $service, string $action, array $params = []);
}
