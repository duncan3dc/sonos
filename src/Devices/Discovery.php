<?php

namespace duncan3dc\Sonos\Devices;

use duncan3dc\Sonos\Interfaces\Devices\CollectionInterface;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use duncan3dc\Sonos\Interfaces\Utils\SocketInterface;
use duncan3dc\Sonos\Utils\Socket;
use Psr\Log\LoggerInterface;

final class Discovery implements CollectionInterface
{
    /**
     * @var CollectionInterface $collection The collection we'll store our discovered devices in.
     */
    private $collection;

    /**
     * @var bool $discovered A flag to indicate whether we've discovered the devices yet or not.
     */
    private $discovered = false;

    /**
     * @var string|int $networkInterface The network interface to use for SSDP discovery.
     */
    private $networkInterface;

    /**
     * @var string $multicastAddress The multicast address to use for SSDP discovery.
     */
    private $multicastAddress = "239.255.255.250";


    /**
     * Create a new instance.
     *
     * @param CollectionInterface $collection The device collection to actually use
     */
    public function __construct(CollectionInterface $collection)
    {
        $this->collection = $collection;
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
     * Set the network interface to use for SSDP discovery.
     *
     * See the documentation on IP_MULTICAST_IF at http://php.net/manual/en/function.socket-get-option.php
     *
     * @param string|int $networkInterface The interface to use
     *
     * @return $this
     */
    public function setNetworkInterface($networkInterface): Discovery
    {
        $this->networkInterface = $networkInterface;
        return $this;
    }


    /**
     * Get the network interface currently in use
     *
     * @return string|int|null The network interface name
     */
    public function getNetworkInterface()
    {
        return $this->networkInterface;
    }


    /**
     * Set the multicast address to use for SSDP discovery.
     *
     * @param string $multicastAddress The address to use
     *
     * @return $this
     */
    public function setMulticastAddress(string $multicastAddress): Discovery
    {
        $this->multicastAddress = $multicastAddress;
        return $this;
    }


    /**
     * Get the multicast address to use for SSDP discovery.
     *
     * @return string The address to use
     */
    public function getMulticastAddress(): string
    {
        return $this->multicastAddress;
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
        return $this;
    }


    /**
     * Get all of the devices on the current network
     *
     * @return DeviceInterface[]
     */
    public function getDevices(): array
    {
        if (!$this->discovered) {
            $socket = new Socket(
                $this->getNetworkInterface(),
                $this->getMulticastAddress(),
                $this->collection->getLogger()
            );
            $this->discoverDevices($socket);
            $this->discovered = true;
        }

        return $this->collection->getDevices();
    }


    /**
     * Get all the devices on the current network.
     *
     * @param SocketInterface $socket An instance to send the discovery request via
     *
     * @return void
     */
    private function discoverDevices(SocketInterface $socket)
    {
        $this->collection->getLogger()->info("discovering devices...");

        $response = $socket->request();

        $search = "urn:schemas-upnp-org:device:ZonePlayer:1";

        $devices = [];
        foreach (explode("\r\n\r\n", $response) as $reply) {
            if (!$reply) {
                continue;
            }

            # Only attempt to parse responses from Sonos speakers
            if (strpos($reply, $search) === false) {
                continue;
            }

            $data = [];
            foreach (explode("\r\n", $reply) as $line) {
                if (!$pos = strpos($line, ":")) {
                    continue;
                }
                $key = strtolower(substr($line, 0, $pos));
                $val = trim(substr($line, $pos + 1));
                $data[$key] = $val;
            }
            $devices[] = $data;
        }

        $unique = [];
        foreach ($devices as $device) {
            if ($device["st"] !== $search) {
                continue;
            }
            if (in_array($device["usn"], $unique)) {
                continue;
            }
            $this->collection->getLogger()->info("found device: {usn}", $device);

            $unique[] = $device["usn"];

            $url = parse_url($device["location"]);
            $this->collection->addIp($url["host"]);
        }
    }


    /**
     * Remove all devices from this collection.
     *
     * @return $this
     */
    public function clear(): CollectionInterface
    {
        $this->collection->clear();

        return $this;
    }
}
