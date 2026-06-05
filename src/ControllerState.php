<?php

namespace duncan3dc\Sonos;

use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\Interfaces\ControllerStateInterface;
use duncan3dc\Sonos\Interfaces\PlayState;
use duncan3dc\Sonos\Interfaces\TrackInterface;
use duncan3dc\Sonos\Interfaces\Utils\TimeInterface;
use duncan3dc\Sonos\Tracks\Stream;

/**
 * Representation of the current state of a controller.
 */
final class ControllerState implements ControllerStateInterface
{
    private PlayState $state;

    /**
     * @var int $track The zero-based number of the track in the queue.
     */
    private int $track;

    /**
     * @var TimeInterface $position The position of the currently active track.
     */
    private TimeInterface $position;

    /**
     * @var bool $repeat Whether repeat mode is currently active.
     */
    private bool $repeat;

    /**
     * @var bool $shuffle Whether shuffle is currently active.
     */
    private bool $shuffle;

    /**
     * @var bool $crossfade Whether crossfade is currently active.
     */
    private bool $crossfade;

    /**
     * @var array<string,int> $speakers The volume of each speaker.
     */
    private array $speakers;

    /**
     * @var TrackInterface[] $tracks An array of tracks from the queue.
     */
    private array $tracks;

    /**
     * @var Stream $stream A stream object (if the controller is currently streaming).
     */
    private Stream $stream;

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
     * @return $this
     */
    public function setState(PlayState $state): ControllerStateInterface
    {
        $this->state = $state;
        return $this;
    }


    /**
     * Get the playing mode of the controller.
     */
    public function getState(): PlayState
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
     */
    public function getPosition(): TimeInterface
    {
        return $this->position;
    }


    /**
     * Check if repeat is currently active.
     */
    public function getRepeat(): bool
    {
        return $this->repeat;
    }


    /**
     * Check if shuffle is currently active.
     */
    public function getShuffle(): bool
    {
        return $this->shuffle;
    }


    /**
     * Check if crossfade is currently active.
     */
    public function getCrossfade(): bool
    {
        return $this->crossfade;
    }


    /**
     * Get the speakers that are in the group of this controller.
     *
     * @return array<string, int>
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
     */
    public function getStream(): ?Stream
    {
        return $this->stream;
    }
}
