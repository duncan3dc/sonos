<?php

namespace duncan3dc\Sonos\Interfaces\Devices;

use duncan3dc\Dom\Xml\ParserInterface;

interface DeviceInterface
{
    /**
     * Get the IP address of this device.
     *
     * @return string
     */
    public function getIp();


    /**
     * Retrieve some xml from the device.
     *
     * @param string $url The url to retrieve
     */
    public function getXml(string $url): ParserInterface;


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


    /**
     * Get the model of this device.
     *
     * @return string
     */
    public function getModel(): string;


    /**
     * Check if this sonos device is a speaker.
     *
     * @return bool
     */
    public function isSpeaker(): bool;
}
