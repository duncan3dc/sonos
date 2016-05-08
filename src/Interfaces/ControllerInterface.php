<?php

namespace duncan3dc\Sonos\Interfaces;

use duncan3dc\Sonos\ControllerState;
use duncan3dc\Sonos\Interfaces\UriInterface;
use duncan3dc\Sonos\Interfaces\Utils\TimeInterface;
use duncan3dc\Sonos\Queue;
use duncan3dc\Sonos\State;
use duncan3dc\Sonos\Tracks\Stream;

/**
 * Allows interaction with the groups of speakers.
 *
 * Although sometimes a Controller is synonymous with a Speaker, when speakers are grouped together only the coordinator can receive events (play/pause/etc)
 */
interface ControllerInterface extends SpeakerInterface
{
    /**
     * No music playing, but not paused.
     *
     * This is a rare state, but can be encountered after an upgrade, or if the queue was cleared
     */
    const STATE_STOPPED = 201;

    /**
     * Currently plating music.
     */
    const STATE_PLAYING = 202;

    /**
     * Music is currently paused.
     */
    const STATE_PAUSED = 203;

    /**
     * The speaker is currently working on either playing or pausing.
     *
     * Check it's state again in a second or two
     */
    const STATE_TRANSITIONING = 204;

    /**
     * The speaker is in an unknown state.
     *
     * This should only happen if Sonos introduce a new state that this code has not been updated to handle.
     */
    const STATE_UNKNOWN = 205;


    /**
     * Get the current state of the group of speakers as the string reported by sonos: PLAYING, PAUSED_PLAYBACK, etc
     *
     * @return string
     */
    public function getStateName(): string;


    /**
     * Get the current state of the group of speakers.
     *
     * @return int One of the class STATE_ constants
     */
    public function getState(): int;


    /**
     * Get attributes about the currently active track in the queue.
     *
     * @return State
     */
    public function getStateDetails(): State;


    /**
     * Set the state of the group.
     *
     * @param int $state One of the class STATE_ constants
     *
     * @return self
     */
    public function setState(int $state): ControllerInterface;


    /**
     * Start playing the active music for this group.
     *
     * @return self
     */
    public function play(): ControllerInterface;


    /**
     * Pause the group.
     *
     * @return self
     */
    public function pause(): ControllerInterface;


    /**
     * Skip to the next track in the current queue.
     *
     * @return self
     */
    public function next(): ControllerInterface;


    /**
     * Skip back to the previous track in the current queue.
     *
     * @return self
     */
    public function previous(): ControllerInterface;


    /**
     * Skip to the specific track in the current queue.
     *
     * @param int $position The zero-based position of the track to skip to
     *
     * @return self
     */
    public function selectTrack(int $position): ControllerInterface;


    /**
     * Seeks to a specific position within the current track.
     *
     * @param TimeInterface $position The position to seek to in the track
     *
     * @return self
     */
    public function seek(TimeInterface $position): ControllerInterface;


    /**
     * Get the currently active media info.
     *
     * @return array
     */
    public function getMediaInfo(): array;


    /**
     * Check if this controller is currently playing a stream.
     *
     * @return bool
     */
    public function isStreaming(): bool;


    /**
     * Play a stream on this controller.
     *
     * @param Stream $stream The Stream object to play
     *
     * @return self
     */
    public function useStream(Stream $stream): ControllerInterface;


    /**
     * Play a line-in from a speaker.
     *
     * If no speaker is passed then the current controller's is used.
     *
     * @param SpeakerInterface|null $speaker The speaker to get the line-in from
     *
     * @return self
     */
    public function useLineIn(SpeakerInterface $speaker = null): ControllerInterface;


    /**
     * Check if this controller is currently using its queue.
     *
     * @return bool
     */
    public function isUsingQueue(): bool;


    /**
     * Set this controller to use its queue (rather than a stream).
     *
     * @return self
     */
    public function useQueue(): ControllerInterface;


    /**
     * Get the speakers that are in the group of this controller.
     *
     * @return SpeakerInterface[]
     */
    public function getSpeakers(): array;


    /**
     * Adds the specified speaker to the group of this Controller.
     *
     * @param SpeakerInterface $speaker The speaker to add to the group
     *
     * @return self
     */
    public function addSpeaker(SpeakerInterface $speaker): ControllerInterface;


    /**
     * Removes the specified speaker from the group of this Controller.
     *
     * @param SpeakerInterface $speaker The speaker to remove from the group
     *
     * @return self
     */
    public function removeSpeaker(SpeakerInterface $speaker): ControllerInterface;


    /**
     * Get the current play mode settings.
     *
     * @return array An array with 2 boolean elements (shuffle and repeat)
     */
    public function getMode(): array;


    /**
     * Set the current play mode settings.
     *
     * @param array $options An array with 2 boolean elements (shuffle and repeat)
     *
     * @return self
     */
    public function setMode(array $options): ControllerInterface;


    /**
     * Check if repeat is currently active.
     *
     * @return bool
     */
    public function getRepeat(): bool;


    /**
     * Turn repeat mode on or off.
     *
     * @param bool $repeat Whether repeat should be on or not
     *
     * @return self
     */
    public function setRepeat(bool $repeat): ControllerInterface;


    /**
     * Check if shuffle is currently active.
     *
     * @return bool
     */
    public function getShuffle(): bool;


    /**
     * Turn shuffle mode on or off.
     *
     * @param bool $shuffle Whether shuffle should be on or not
     *
     * @return self
     */
    public function setShuffle(bool $shuffle): ControllerInterface;


    /**
     * Check if crossfade is currently active.
     *
     * @return bool
     */
    public function getCrossfade(): bool;


    /**
     * Turn crossfade on or off.
     *
     * @param bool $crossfade Whether crossfade should be on or not
     *
     * @return self
     */
    public function setCrossfade(bool $crossfade): self;


    /**
     * Get the queue for this controller.
     *
     * @return Queue
     */
    public function getQueue(): Queue;


    /**
     * Grab the current state of the Controller (including it's queue and playing attributes).
     *
     * @param bool $pause Whether to pause the controller or not
     *
     * @return ControllerState
     */
    public function exportState(bool $pause = true): ControllerState;


    /**
     * Restore the Controller to a previously exported state.
     *
     * @param ControllerState $state The state to be restored
     *
     * @return self
     */
    public function restoreState(ControllerState $state): ControllerInterface;


    /**
     * Interrupt the current audio with a track.
     *
     * The current state of the controller is stored,
     * the passed track is played, and then when it has finished
     * the previous state of the controller is restored.
     * This is useful for making announcements over the Sonos network.
     *
     * @param UriInterface $track The track to play
     * @param int $volume The volume to play the track at
     *
     * @return self
     */
    public function interrupt(UriInterface $track, int $volume = null): ControllerInterface;
}
