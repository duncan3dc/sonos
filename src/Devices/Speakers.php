<?php

namespace duncan3dc\Sonos\Devices;

use duncan3dc\Sonos\Exceptions\InvalidArgumentException;
use duncan3dc\Sonos\Interfaces\Devices\CollectionInterface;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use Psr\Log\LoggerInterface;

use function array_key_exists;
use function array_pop;

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
        "S33" => "SYMFONISK Bookshelf",
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

    private CollectionInterface $collection;


    public function __construct(CollectionInterface $collection)
    {
        $this->collection = $collection;
    }


    public function setLogger(LoggerInterface $logger): void
    {
        $this->collection->setLogger($logger);
    }


    public function getLogger(): LoggerInterface
    {
        return $this->collection->getLogger();
    }


    private function isSpeaker(DeviceInterface $device): bool
    {
        return array_key_exists($device->getModel(), self::MODELS);
    }


    public function addDevice(DeviceInterface $device): CollectionInterface
    {
        if (!$this->isSpeaker($device)) {
            throw new InvalidArgumentException("This device is not recognised as a speaker model: " . $device->getModel());
        }

        $this->collection->addDevice($device);
        return $this;
    }


    public function addIp(string $address): CollectionInterface
    {
        $this->collection->addIp($address);

        $devices = $this->collection->getDevices();
        $device = array_pop($devices);
        assert($device instanceof DeviceInterface);
        if (!$this->isSpeaker($device)) {
            throw new InvalidArgumentException("This device is not recognised as a speaker model: " . $device->getModel());
        }

        return $this;
    }


    /**
     * Get all of the devices that can be used as speakers on the current network
     *
     * @return DeviceInterface[]
     */
    public function getDevices(): array
    {
        $speakers = [];

        foreach ($this->collection->getDevices() as $device) {
            if ($this->isSpeaker($device)) {
                $speakers[] = $device;
            }
        }

        return $speakers;
    }


    public function clear(): CollectionInterface
    {
        $this->collection->clear();
        return $this;
    }
}
