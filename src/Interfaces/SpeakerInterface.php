<?php

namespace duncan3dc\Sonos\Interfaces;

/**
 * Represents an individual Sonos speaker, to allow volume, equalisation, and other settings to be managed.
 */
interface SpeakerInterface
{
    /**
     * Send a soap request to the speaker.
     *
     * @param string $service The service to send the request to
     * @param string $action The action to call
     * @param array $params The parameters to pass
     *
     * @return mixed
     * @internal
     */
    public function soap(string $service, string $action, array $params = []);

    /**
     * Get the IP address of this speaker.
     *
     * @return string
     */
    public function getIp(): string;

    /**
     * Get the "Friendly" name of this speaker.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the room name of this speaker.
     *
     * @return string
     */
    public function getRoom(): string;


    /**
     * Get the uuid of the group this speaker is a member of.
     *
     * @return string
     */
    public function getGroup(): string;

    /**
     * Check if this speaker is the coordinator of it's current group.
     *
     * @return bool
     */
    public function isCoordinator(): bool;

    /**
     * Get the uuid of this speaker.
     *
     * @return string The uuid of this speaker
     */
    public function getUuid(): string;

    /**
     * Get the current volume of this speaker.
     *
     * @return int The current volume between 0 and 100
     */
    public function getVolume(): int;

    /**
     * Adjust the volume of this speaker to a specific value.
     *
     * @param int $volume The amount to set the volume to between 0 and 100
     *
     * @return $this
     */
    public function setVolume(int $volume): SpeakerInterface;

    /**
     * Adjust the volume of this speaker by a relative amount.
     *
     * @param int $adjust The amount to adjust by between -100 and 100
     *
     * @return $this
     */
    public function adjustVolume(int $adjust): SpeakerInterface;

    /**
     * Check if this speaker is currently muted.
     *
     * @return bool
     */
    public function isMuted(): bool;

    /**
     * Mute this speaker.
     *
     * @param bool $mute Whether the speaker should be muted or not
     *
     * @return $this
     */
    public function mute(bool $mute = true): SpeakerInterface;

    /**
     * Unmute this speaker.
     *
     * @return $this
     */
    public function unmute(): SpeakerInterface;

    /**
     * Turn the indicator light on or off.
     *
     * @param bool $on Whether the indicator should be on or off
     *
     * @return $this
     */
    public function setIndicator(bool $on): SpeakerInterface;

    /**
     * Check whether the indicator light is on or not.
     *
     * @return bool
     */
    public function getIndicator(): bool;

    /**
     * Get the treble equalisation level.
     *
     * @return int
     */
    public function getTreble(): int;

    /**
     * Set the treble equalisation.
     *
     * @param int $treble The treble level (between -10 and 10)
     *
     * @return $this
     */
    public function setTreble(int $treble): SpeakerInterface;

    /**
     * Get the bass equalisation level.
     *
     * @return int
     */
    public function getBass(): int;

    /**
     * Set the bass equalisation.
     *
     * @param int $bass The bass level (between -10 and 10)
     *
     * @return $this
     */
    public function setBass(int $bass): SpeakerInterface;

    /**
     * Check whether loudness normalisation is on or not.
     *
     * @return bool
     */
    public function getLoudness(): bool;

    /**
     * Set whether loudness normalisation is on or not.
     *
     * @param bool $on Whether loudness should be on or not
     *
     * @return $this
     */
    public function setLoudness(bool $on): SpeakerInterface;
}
