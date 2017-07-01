<?php

namespace duncan3dc\Sonos;

use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\Interfaces\ControllerStateInterface;
use duncan3dc\Sonos\Interfaces\SpeakerInterface;
use duncan3dc\Sonos\Interfaces\TrackInterface;
use duncan3dc\Sonos\Tracks\Stream;
use duncan3dc\Sonos\Utils\Time;

/**
 * Representation of the current state of a controller.
 */
final class ControllerState implements ControllerStateInterface
{
    /**
     * @var int One of the ControllerInterface::STATE_ constants
     */
    private $state;

    /**
     * @var int $track The zero-based number of the track in the queue.
     */
    private $track;

    /**
     * @var string $position The position of the currently active track (hh:mm:ss).
     */
    private $position;

    /**
     * @var bool $repeat Whether repeat mode is currently active.
     */
    private $repeat;

    /**
     * @var bool $shuffle Whether shuffle is currently active.
     */
    private $shuffle;

    /**
     * @var bool $crossfade Whether crossfade is currently active.
     */
    private $crossfade;

    /**
     * @var SpeakerInterface[] $speakers Each speaker that is managed by this controller.
     */
    private $speakers;

    /**
     * @var TrackInterface[] $tracks An array of tracks from the queue.
     */
    private $tracks;

    /**
     * @var Stream $stream A stream object (if the controller is currently streaming).
     */
    private $stream;

    /**
     * Create a ControllerState object.
     *
     * @param ControllerInterface $controller The Controller to grab the state of
     */
    public function __construct(ControllerInterface $controller)
    {
        $this
            ->applyState($controller)
            ->applyMode($controller)
            ->applyVolume($controller)
            ->applyTracks($controller);
    }


    /**
     * Get the current playing attributes (stream/position/etc).
     *
     * @param ControllerInterface $controller The Controller to grab the state of
     *
     * @return $this
     */
    private function applyState(ControllerInterface $controller): ControllerStateInterface
    {
        $this->state = $controller->getState();

        $details = $controller->getStateDetails();
        $this->track = $details->getNumber();
        $this->position = $details->getPosition();

        return $this;
    }


    /**
     * Get the current playing mode (repeat/shuffle/etc).
     *
     * @param ControllerInterface $controller The Controller to grab the state of
     *
     * @return $this
     */
    private function applyMode(ControllerInterface $controller): ControllerStateInterface
    {
        $mode = $controller->getMode();
        $this->repeat = $mode["repeat"];
        $this->shuffle = $mode["shuffle"];

        $this->crossfade = $controller->getCrossfade();

        return $this;
    }


    /**
     * Get the current volume of all the speakers in this group.
     *
     * @param ControllerInterface $controller The Controller to grab the state of
     *
     * @return $this
     */
    private function applyVolume(ControllerInterface $controller): ControllerStateInterface
    {
        $this->speakers = [];
        foreach ($controller->getSpeakers() as $speaker) {
            $this->speakers[$speaker->getUuid()] = $speaker->getVolume();
        }

        return $this;
    }


    /**
     * Get the current tracks in the queue.
     *
     * @param ControllerInterface $controller The Controller to grab the state of
     *
     * @return $this
     */
    private function applyTracks(ControllerInterface $controller): ControllerStateInterface
    {
        $this->tracks = $controller->getQueue()->getTracks();

        if ($controller->isStreaming()) {
            $media = $controller->getMediaInfo();
            $this->stream = new Stream($media["CurrentURI"]);
        }

        return $this;
    }


    /**
     * Set the playing mode of the controller.
     *
     * $param int $state One of the ControllerInterface::STATE_ constants
     *
     * @return $this
     */
    public function setState($state): ControllerStateInterface
    {
        $this->state = $state;
        return $this;
    }


    /**
     * Get the playing mode of the controller.
     *
     * @return int One of the ControllerInterface::STATE_ constants
     */
    public function getState(): int
    {
        return $this->state;
    }


    /**
     * Get the number of the active track in the queue
     *
     * @return int The zero-based number of the track in the queue
     */
    public function getTrack(): int
    {
        return $this->track;
    }


    /**
     * Get the position of the currently active track.
     *
     * @return Time
     */
    public function getPosition(): Time
    {
        return $this->position;
    }


    /**
     * Check if repeat is currently active.
     *
     * @return bool
     */
    public function getRepeat(): bool
    {
        return $this->repeat;
    }


    /**
     * Check if shuffle is currently active.
     *
     * @return bool
     */
    public function getShuffle(): bool
    {
        return $this->shuffle;
    }


    /**
     * Check if crossfade is currently active.
     *
     * @return bool
     */
    public function getCrossfade(): bool
    {
        return $this->crossfade;
    }


    /**
     * Get the speakers that are in the group of this controller.
     *
     * @return SpeakerInterface[]
     */
    public function getSpeakers(): array
    {
        return $this->speakers;
    }


    /**
     * Get the tracks that are in the queue.
     *
     * @return TrackInterface[]
     */
    public function getTracks(): array
    {
        return $this->tracks;
    }


    /**
     * Get the stream this controller is using.
     *
     * @var Stream|null
     */
    public function getStream()
    {
        return $this->stream;
    }
}
