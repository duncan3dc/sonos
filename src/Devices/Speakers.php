<?php

namespace duncan3dc\Sonos\Devices;

use duncan3dc\Sonos\Interfaces\Devices\CollectionInterface;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use Psr\Log\LoggerInterface;

use function array_key_exists;

/**
 * Use discovery but only for known speaker models
 */
final class Speakers implements CollectionInterface
{
    private const MODELS = [
        "S1" => "PLAY:1",
        "S12" => "PLAY:1",
        "S3" => "PLAY:3",
        "S5" => "PLAY:5",
        "S6" => "PLAY:5",
        "S24" => "PLAY:5",
        "S9" => "PLAYBAR",
        "S11" => "PLAYBASE",
        "S13" => "ONE",
        "S18" => "ONE",
        "S14" => "BEAM",
        "S31" => "BEAM",
        "S15" => "CONNECT",
        "S17" => "Move",
        "S19" => "ARC",
        "S20" => "SYMFONISK Table Lamp",
        "S21" => "SYMFONISK Bookshelf",
        "S29" => "SYMFONISK Picture Frame",
        "S22" => "ONE SL",
        "S38" => "ONE SL",
        "S23" => "PORT",
        "S27" => "ROAM",
        "S35" => "ROAM SL",
        "ZP80" => "ZONEPLAYER",
        "ZP90" => "CONNECT",
        "S16" => "CONNECT:AMP",
        "ZP100" => "CONNECT:AMP",
        "ZP120" => "CONNECT:AMP",
    ];

    /**
     * @var CollectionInterface $collection The device collection to actually use.
     */
    private $collection;


    /**
     * @param CollectionInterface $collection The device collection to actually use
     */
    public function __construct(CollectionInterface $collection)
    {
        $this->collection = $collection;
    }


    /**
     * Set the logger object to use.
     *
     * @return $this
     * @var LoggerInterface $logger The logging object
     *
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


    private function isSpeaker(DeviceInterface $device): bool
    {
        return array_key_exists($device->getModel(), self::MODELS);
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
        if (!$this->isSpeaker($device)) {
            $error = "This device is not recognised as a speaker model: " . $device->getModel();
            throw new \InvalidArgumentException($error);
        }

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
        return $this;
    }
}
