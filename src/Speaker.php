<?php

namespace duncan3dc\Sonos;

use duncan3dc\Sonos\Devices\Device;
use duncan3dc\Sonos\Exceptions\UnknownGroupException;
use duncan3dc\Sonos\Interfaces\Devices\DeviceInterface;
use duncan3dc\Sonos\Interfaces\SpeakerInterface;
use duncan3dc\Sonos\Utils\SoapResponse;

use function explode;
use function strpos;

/**
 * Represents an individual Sonos speaker, to allow volume, equalisation, and other settings to be managed.
 */
final class Speaker implements SpeakerInterface
{
    /**
     * @var string $ip The IP address of the speaker.
     */
    private string $ip;

    /**
     * @var DeviceInterface $device The instance of the Device class to send requests to.
     */
    private DeviceInterface $device;

    /**
     * @var ?string $group The group id this speaker is a part of.
     */
    private ?string $group = null;

    /**
     * @var bool $coordinator Whether this speaker is the coordinator of its group or not.
     */
    private bool $coordinator = false;


    /**
     * Create an instance of the Speaker class.
     *
     * @param DeviceInterface|string $param An Device instance or the ip address that the speaker is listening on
     */
    public function __construct(DeviceInterface|string $param)
    {
        if ($param instanceof DeviceInterface) {
            $this->device = $param;
            $this->ip = $this->device->getIp();
        } else {
            $this->ip = $param;
            $this->device = new Device($this->ip);
        }
    }


    public function soap(string $service, string $action, array $params = []): SoapResponse
    {
        return $this->device->soap($service, $action, $params);
    }


    /**
     * Get the IP address of this speaker.
     */
    public function getIp(): string
    {
        return $this->ip;
    }


    /**
     * Get the "Friendly" name of this speaker.
     */
    public function getName(): string
    {
        return $this->device->getName();
    }


    /**
     * Get the room name of this speaker.
     */
    public function getRoom(): string
    {
        return $this->device->getRoom();
    }


    /**
     * Ensure we've determined this speaker's topology.
     */
    private function lookupTopology(): void
    {
        if ($this->group !== null) {
            return;
        }

        $attributes = $this->soap("ZoneGroupTopology", "GetZoneGroupAttributes")->getArray();

        $this->setGroup($attributes["CurrentZoneGroupID"]);

        $this->coordinator = false;
        if (strpos($attributes["CurrentZonePlayerUUIDsInGroup"], ",") === false) {
            $this->coordinator = true;
        } else {
            list($uuid) = explode(":", $attributes["CurrentZoneGroupID"]);
            if ($uuid === $this->getUuid()) {
                $this->coordinator = true;
            }
        }
    }


    /**
     * @inheritDoc
     */
    public function getGroup(): string
    {
        $this->lookupTopology();

        return (string) $this->group;
    }


    /**
     * @inheritDoc
     */
    public function updateGroup(): void
    {
        $this->group = null;
    }


    /**
     * @inheritDoc
     */
    public function setGroup(string $group): void
    {
        if ($group === "") {
            throw new UnknownGroupException("Unable to figure out the group of this speaker");
        }

        $this->group = $group;
    }


    /**
     * Check if this speaker is the coordinator of it's current group.
     */
    public function isCoordinator(): bool
    {
        $this->lookupTopology();

        return $this->coordinator;
    }


    /**
     * Get the uuid of this speaker.
     */
    public function getUuid(): string
    {
        return $this->device->getUuid();
    }


    /**
     * Get the current volume of this speaker.
     */
    public function getVolume(): int
    {
        $result = $this->soap("RenderingControl", "GetVolume", [
            "Channel" => "Master",
        ]);
        return $result->getInteger();
    }


    /**
     * Adjust the volume of this speaker to a specific value.
     *
     * @param int $volume The amount to set the volume to between 0 and 100
     *
     * @return $this
     */
    public function setVolume(int $volume): SpeakerInterface
    {
        $this->soap("RenderingControl", "SetVolume", [
            "Channel"       =>  "Master",
            "DesiredVolume" =>  $volume,
        ]);

        return $this;
    }


    /**
     * Adjust the volume of this speaker by a relative amount.
     *
     * @param int $adjust The amount to adjust by between -100 and 100
     *
     * @return $this
     */
    public function adjustVolume(int $adjust): SpeakerInterface
    {
        $this->soap("RenderingControl", "SetRelativeVolume", [
            "Channel"       =>  "Master",
            "Adjustment"    =>  $adjust,
        ]);

        return $this;
    }


    /**
     * Check if this speaker is currently muted.
     */
    public function isMuted(): bool
    {
        return $this->soap("RenderingControl", "GetMute", [
            "Channel"   =>  "Master",
        ])->getBoolean();
    }


    /**
     * Mute this speaker.
     *
     * @param bool $mute Whether the speaker should be muted or not
     *
     * @return $this
     */
    public function mute(bool $mute = true): SpeakerInterface
    {
        $this->soap("RenderingControl", "SetMute", [
            "Channel"       =>  "Master",
            "DesiredMute"   =>  $mute ? 1 : 0,
        ]);

        return $this;
    }


    /**
     * Unmute this speaker.
     *
     * @return $this
     */
    public function unmute(): SpeakerInterface
    {
        return $this->mute(false);
    }


    /**
     * Turn the indicator light on or off.
     *
     * @param bool $on Whether the indicator should be on or off
     *
     * @return $this
     */
    public function setIndicator(bool $on): SpeakerInterface
    {
        $this->soap("DeviceProperties", "SetLEDState", [
            "DesiredLEDState"   =>  $on ? "On" : "Off",
        ]);

        return $this;
    }


    /**
     * Check whether the indicator light is on or not.
     */
    public function getIndicator(): bool
    {
        return ($this->soap("DeviceProperties", "GetLEDState")->getString() === "On");
    }


    /**
     * Set the bass/treble equalisation level.
     *
     * @param string $type Which setting to update (bass or treble)
     * @param int $value The value to set (between -10 and 10)
     *
     * @return $this
     */
    private function setEqLevel(string $type, int $value): SpeakerInterface
    {
        if ($value < -10) {
            $value = -10;
        }
        if ($value > 10) {
            $value = 10;
        }

        $type = ucfirst(strtolower($type));
        $this->soap("RenderingControl", "Set{$type}", [
            "Channel"           =>  "Master",
            "Desired{$type}"    =>  $value,
        ]);

        return $this;
    }

    /**
     * Get the treble equalisation level.
     */
    public function getTreble(): int
    {
        return $this->soap("RenderingControl", "GetTreble", [
            "Channel" => "Master",
        ])->getInteger();
    }


    /**
     * Set the treble equalisation.
     *
     * @param int $treble The treble level (between -10 and 10)
     *
     * @return $this
     */
    public function setTreble(int $treble): SpeakerInterface
    {
        return $this->setEqLevel("treble", $treble);
    }


    /**
     * Get the bass equalisation level.
     */
    public function getBass(): int
    {
        return $this->soap("RenderingControl", "GetBass", [
            "Channel" => "Master",
        ])->getInteger();
    }


    /**
     * Set the bass equalisation.
     *
     * @param int $bass The bass level (between -10 and 10)
     *
     * @return $this
     */
    public function setBass(int $bass): SpeakerInterface
    {
        return $this->setEqLevel("bass", $bass);
    }


    /**
     * Check whether loudness normalisation is on or not.
     */
    public function getLoudness(): bool
    {
        return $this->soap("RenderingControl", "GetLoudness", [
            "Channel"       =>  "Master",
        ])->getBoolean();
    }


    /**
     * Set whether loudness normalisation is on or not.
     *
     * @param bool $on Whether loudness should be on or not
     */
    public function setLoudness(bool $on): SpeakerInterface
    {
        $this->soap("RenderingControl", "SetLoudness", [
            "Channel"           =>  "Master",
            "DesiredLoudness"   =>  $on ? 1 : 0,
        ]);

        return $this;
    }
}
