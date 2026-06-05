<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Exceptions\InvalidArgumentException;
use duncan3dc\Sonos\Exceptions\SoapException;
use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\Interfaces\ControllerStateInterface;
use duncan3dc\Sonos\Interfaces\NetworkInterface;
use duncan3dc\Sonos\Interfaces\PlayState;
use duncan3dc\Sonos\Interfaces\QueueInterface;
use duncan3dc\Sonos\Interfaces\SpeakerInterface;
use duncan3dc\Sonos\Interfaces\StateInterface;
use duncan3dc\Sonos\Interfaces\UriInterface;
use duncan3dc\Sonos\Interfaces\Utils\TimeInterface;
use duncan3dc\Sonos\Tracks\Stream;
use duncan3dc\Sonos\Utils\SoapResponse;
use duncan3dc\Sonos\Utils\Time;

use function strpos;

/**
 * Allows interaction with the groups of speakers.
 *
 * Although sometimes a Controller is synonymous with a Speaker,
 * when speakers are grouped together only the coordinator can receive events (play/pause/etc)
 */
final class Controller implements ControllerInterface
{
    /**
     * @var NetworkInterface $network The network instance this Controller is part of.
     */
    private NetworkInterface $network;


    /**
     * @var SpeakerInterface $speaker The underlying speaker instance for this controller.
     */
    private SpeakerInterface $speaker;


    /**
     * Create a Controller instance from a speaker.
     *
     * The speaker must be a coordinator.
     *
     * @param SpeakerInterface $speaker
     */
    public function __construct(SpeakerInterface $speaker, NetworkInterface $network)
    {
        if (!$speaker->isCoordinator()) {
            throw new InvalidArgumentException("You cannot create a Controller instance from a Speaker that is not the coordinator of its group");
        }

        $this->network = $network;
        $this->speaker = $speaker;
    }


    /**
     * Get the current state of the group of speakers as the string reported by sonos: PLAYING, PAUSED_PLAYBACK, etc
     */
    public function getStateName(): string
    {
        $data = $this->soap("AVTransport", "GetTransportInfo")->getArray();
        return $data["CurrentTransportState"];
    }


    /**
     * Get the current state of the group of speakers.
     */
    public function getState(): PlayState
    {
        $name = $this->getStateName();
        switch ($name) {
            case "STOPPED":
                return PlayState::Stopped;
            case "PLAYING":
                return PlayState::Playing;
            case "PAUSED_PLAYBACK":
                return PlayState::Paused;
            case "TRANSITIONING":
                return PlayState::Transitioning;
        }
        return PlayState::Unknown;
    }


    /**
     * Get attributes about the currently active track in the queue.
     */
    public function getStateDetails(): StateInterface
    {
        $data = $this->soap("AVTransport", "GetPositionInfo")->getArray();

        # Check for line in mode
        if ($data["TrackMetaData"] === "NOT_IMPLEMENTED") {
            $state = new State($data["TrackURI"]);
            $state->setStream(new Stream("x-rincon-stream:" . $this->getUuid(), "Line-In"));
            return $state;
        }

        # Check for an empty queue
        if (!$data["TrackMetaData"]) {
            return new State();
        }

        $parser = new XmlParser($data["TrackMetaData"]);
        $state = State::createFromXml($parser->getTag("item"), $this);

        if ((string) $parser->getTag("streamContent")) {
            $info = $this->getMediaInfo();
            $meta = new XmlParser($info["CurrentURIMetaData"]);
            if ($title = (string) $meta->getTag("title")) {
                $state->setStream(new Stream("", $title));
            } else {
                $state->setStream(new Stream("", $parser->getTag("title")));
            }
        }

        $state->setNumber($data["Track"]);
        $state->setDuration(Time::parse($data["TrackDuration"]));
        $state->setPosition(Time::parse($data["RelTime"]));

        # If we have a queue number, it'll be one-based, rather than zero-based, so convert it
        if ($state->getNumber() > 0) {
            $state->setNumber($state->getNumber() - 1);
        }

        return $state;
    }


    /**
     * Set the state of the group.
     *
     * @return $this
     */
    public function setState(PlayState $state): ControllerInterface
    {
        switch ($state) {
            case PlayState::Playing:
                return $this->play();
            case PlayState::Paused:
                return $this->pause();
            case PlayState::Stopped:
                return $this->pause();
        }
        throw new InvalidArgumentException("Unknown state");
    }


    /**
     * Start playing the active music for this group.
     *
     * @return $this
     */
    public function play(): ControllerInterface
    {
        try {
            $this->soap("AVTransport", "Play", [
                "Speed" =>  1,
            ]);
        } catch (SoapException $e) {
            if (count($this->getQueue()) < 1) {
                $e = new \BadMethodCallException("Cannot play, the current queue is empty");
            }
            throw $e;
        }

        return $this;
    }


    /**
     * Pause the group.
     *
     * @return $this
     */
    public function pause(): ControllerInterface
    {
        $this->soap("AVTransport", "Pause");

        return $this;
    }


    /**
     * Skip to the next track in the current queue.
     *
     * @return $this
     */
    public function next(): ControllerInterface
    {
        $this->soap("AVTransport", "Next");

        return $this;
    }


    /**
     * Skip back to the previous track in the current queue.
     *
     * @return $this
     */
    public function previous(): ControllerInterface
    {
        $this->soap("AVTransport", "Previous");

        return $this;
    }


    /**
     * Skip to the specific track in the current queue.
     *
     * @param int $position The zero-based position of the track to skip to
     *
     * @return $this
     */
    public function selectTrack(int $position): ControllerInterface
    {
        $this->soap("AVTransport", "Seek", [
            "Unit"      =>  "TRACK_NR",
            "Target"    =>  $position + 1,
        ]);

        return $this;
    }


    /**
     * Seeks to a specific position within the current track.
     *
     * @param TimeInterface $position The position to seek to in the track
     *
     * @return $this
     */
    public function seek(TimeInterface $position): ControllerInterface
    {
        $this->soap("AVTransport", "Seek", [
            "Unit"      =>  "REL_TIME",
            "Target"    =>  $position->asString(),
        ]);

        return $this;
    }


    /**
     * @return array<string, string>
     */
    public function getMediaInfo(): array
    {
        return $this->soap("AVTransport", "GetMediaInfo")->getArray();
    }


    /**
     * Check if this controller is currently playing a stream.
     */
    public function isStreaming(): bool
    {
        $prefixes = [
            # Standard streams
            "x-sonosapi-stream:",

            # Other streams (eg Amazon)
            "x-sonosapi-radio:",

            # Line in
            "x-rincon-stream:",

            # Line in (playbar)
            "x-sonos-htastream:",
        ];

        $media = $this->getMediaInfo();

        foreach ($prefixes as $prefix) {
            if (strpos($media["CurrentURI"], $prefix) === 0) {
                return true;
            }
        }

        return false;
    }


    /**
     * Play a stream on this controller.
     *
     * @param Stream $stream The Stream object to play
     *
     * @return $this
     */
    public function useStream(Stream $stream): ControllerInterface
    {
        $this->soap("AVTransport", "SetAVTransportURI", [
            "CurrentURI"            =>  $stream->getUri(),
            "CurrentURIMetaData"    =>  $stream->getMetaData(),
        ]);

        return $this;
    }


    /**
     * Play a line-in from a speaker.
     *
     * If no speaker is passed then the current controller's is used.
     *
     * @param ?SpeakerInterface $speaker The speaker to get the line-in from
     *
     * @return $this
     */
    public function useLineIn(?SpeakerInterface $speaker = null): ControllerInterface
    {
        if ($speaker === null) {
            $speaker = $this;
        }

        $uri = "x-rincon-stream:" . $speaker->getUuid();
        $stream = new Stream($uri, "Line-In");

        return $this->useStream($stream);
    }


    /**
     * Check if this controller is currently using its queue.
     */
    public function isUsingQueue(): bool
    {
        $media = $this->getMediaInfo();

        return (substr($media["CurrentURI"], 0, 15) === "x-rincon-queue:");
    }


    /**
     * Set this controller to use its queue (rather than a stream).
     *
     * @return $this
     */
    public function useQueue(): ControllerInterface
    {
        $this->soap("AVTransport", "SetAVTransportURI", [
            "CurrentURI"            =>  "x-rincon-queue:" . $this->getUuid() . "#0",
            "CurrentURIMetaData"    =>  "",
        ]);

        return $this;
    }


    /**
     * Get the speakers that are in the group of this controller.
     *
     * @return SpeakerInterface[]
     */
    public function getSpeakers(): array
    {
        $group = [];
        $speakers = $this->network->getSpeakers();
        foreach ($speakers as $speaker) {
            if ($speaker->getGroup() === $this->getGroup()) {
                $group[] = $speaker;
            }
        }
        return $group;
    }


    /**
     * Adds the specified speaker to the group of this Controller.
     *
     * @param SpeakerInterface $speaker The speaker to add to the group
     *
     * @return $this
     */
    public function addSpeaker(SpeakerInterface $speaker): ControllerInterface
    {
        if ($speaker->getUuid() === $this->getUuid()) {
            return $this;
        }

        $speaker->soap("AVTransport", "SetAVTransportURI", [
            "CurrentURI"            =>  "x-rincon:" . $this->getUuid(),
            "CurrentURIMetaData"    =>  "",
        ]);

        $speaker->setGroup($speaker->getGroup());

        return $this;
    }


    /**
     * Removes the specified speaker from the group of this Controller.
     *
     * @param SpeakerInterface $speaker The speaker to remove from the group
     *
     * @return $this
     */
    public function removeSpeaker(SpeakerInterface $speaker): ControllerInterface
    {
        $speaker->soap("AVTransport", "BecomeCoordinatorOfStandaloneGroup");

        $speaker->updateGroup();

        return $this;
    }


    public function getMode(): array
    {
        $data = $this->soap("AVTransport", "GetTransportSettings")->getArray();
        return Helper::getMode($data["PlayMode"]);
    }


    public function setMode(array $options): ControllerInterface
    {
        $this->soap("AVTransport", "SetPlayMode", [
            "NewPlayMode"   =>  Helper::setMode($options),
        ]);

        return $this;
    }


    /**
     * Get a particular PlayMode.
     *
     * @param string $type The play mode attribute to get
     */
    private function getPlayMode(string $type): bool
    {
        $mode = $this->getMode();
        return $mode[$type];
    }


    /**
     * Set a particular PlayMode.
     *
     * @param string $type The play mode attribute to update
     * @param bool $value The value to set the attribute to
     *
     * @return $this
     */
    private function setPlayMode(string $type, bool $value): ControllerInterface
    {
        $mode = $this->getMode();
        if ($mode[$type] === $value) {
            return $this;
        }

        $mode[$type] = $value;
        $this->setMode($mode);

        return $this;
    }


    /**
     * Check if repeat is currently active.
     */
    public function getRepeat(): bool
    {
        return $this->getPlayMode("repeat");
    }


    /**
     * Turn repeat mode on or off.
     *
     * @param bool $repeat Whether repeat should be on or not
     *
     * @return $this
     */
    public function setRepeat(bool $repeat): ControllerInterface
    {
        return $this->setPlayMode("repeat", $repeat);
    }


    /**
     * Check if shuffle is currently active.
     */
    public function getShuffle(): bool
    {
        return $this->getPlayMode("shuffle");
    }


    /**
     * Turn shuffle mode on or off.
     *
     * @param bool $shuffle Whether shuffle should be on or not
     *
     * @return $this
     */
    public function setShuffle(bool $shuffle): ControllerInterface
    {
        return $this->setPlayMode("shuffle", $shuffle);
    }


    /**
     * Check if crossfade is currently active.
     */
    public function getCrossfade(): bool
    {
        return $this->soap("AVTransport", "GetCrossfadeMode")->getBoolean();
    }


    /**
     * Turn crossfade on or off.
     *
     * @param bool $crossfade Whether crossfade should be on or not
     *
     * @return $this
     */
    public function setCrossfade(bool $crossfade): ControllerInterface
    {
        $this->soap("AVTransport", "SetCrossfadeMode", [
            "CrossfadeMode" => $crossfade,
        ]);

        return $this;
    }


    /**
     * Get the queue for this controller.
     */
    public function getQueue(): QueueInterface
    {
        return new Queue($this);
    }


    /**
     * Grab the current state of the Controller (including it's queue and playing attributes).
     *
     * @param bool $pause Whether to pause the controller or not
     */
    public function exportState(bool $pause = true): ControllerStateInterface
    {
        if ($pause) {
            $state = $this->getState();
            if ($state === PlayState::Playing) {
                $this->pause();
            }
        }

        $export = new ControllerState($this);

        if ($pause) {
            $export->setState($state);
        }

        return $export;
    }


    /**
     * Restore the Controller to a previously exported state.
     *
     * @param ControllerStateInterface $state The state to be restored
     *
     * @return $this
     */
    public function restoreState(ControllerStateInterface $state): ControllerInterface
    {
        $queue = $this->getQueue();
        $queue->clear();
        $tracks = $state->getTracks();
        if (count($tracks) > 0) {
            $queue->addTracks($tracks);
        }

        if (count($tracks) > 0) {
            $this->selectTrack($state->getTrack());
            $this->seek($state->getPosition());
        }

        $this->setShuffle($state->getShuffle());
        $this->setRepeat($state->getRepeat());
        $this->setCrossfade($state->getCrossfade());

        if ($stream = $state->getStream()) {
            $this->useStream($stream);
        }

        $speakers = [];
        foreach ($this->getSpeakers() as $speaker) {
            $speakers[$speaker->getUuid()] = $speaker;
        }
        foreach ($state->getSpeakers() as $uuid => $volume) {
            if (array_key_exists($uuid, $speakers)) {
                $speakers[$uuid]->setVolume($volume);
            }
        }

        # If the exported state was playing then start it playing now
        if ($state->getState() === PlayState::Playing) {
            $this->play();

        # If the exported state was stopped and we are playing then stop it now
        } elseif ($this->getState() === PlayState::Playing) {
            $this->pause();
        }

        return $this;
    }


    /**
     * Interrupt the current audio with a track.
     *
     * The current state of the controller is stored,
     * the passed track is played, and then when it has finished
     * the previous state of the controller is restored.
     * This is useful for making announcements over the Sonos network.
     *
     * @param UriInterface $track The track to play
     * @param ?int $volume The volume to play the track at
     *
     * @return $this
     */
    public function interrupt(UriInterface $track, ?int $volume = null): ControllerInterface
    {
        /**
         * Ensure the track has been generated.
         * If it's a TextToSpeech then the api call is done lazily when the uri is required.
         * So it's better to do this here, rather than after the controller has been paused.
         */
        $track->getUri();

        $state = $this->exportState();

        # Replace the current queue with the passed track
        $this->useQueue()->getQueue()->clear()->addTrack($track);

        # Ensure repeat is not on, or else this track would just play indefinitely
        $this->setRepeat(false);

        # If a volume was passed then use it
        if ($volume !== null) {
            $this->setVolume($volume);
        }

        # Play the track
        $this->play();

        # Sleep first so that the track has a chance to at least start
        sleep(1);

        # Wait for the track to finish
        while ($this->getState() === PlayState::Playing) {
            usleep(500000);
        }

        # Restore the previous state of this controller
        $this->restoreState($state);

        return $this;
    }


    public function soap(string $service, string $action, array $params = []): SoapResponse
    {
        return $this->speaker->soap($service, $action, $params);
    }


    /**
     * Get the IP address of this speaker.
     */
    public function getIp(): string
    {
        return $this->speaker->getIp();
    }


    /**
     * Get the "Friendly" name of this speaker.
     */
    public function getName(): string
    {
        return $this->speaker->getName();
    }


    /**
     * Get the room name of this speaker.
     */
    public function getRoom(): string
    {
        return $this->speaker->getRoom();
    }


    /**
     * @inheritDoc
     */
    public function getGroup(): string
    {
        return $this->speaker->getGroup();
    }


    /**
     * @inheritDoc
     */
    public function updateGroup(): void
    {
        $this->speaker->updateGroup();
    }


    /**
     * @inheritDoc
     */
    public function setGroup(string $group): void
    {
        $this->speaker->setGroup($group);
    }


    /**
     * Check if this speaker is the coordinator of it's current group.
     *
     * This method is only here for SpeakerInterface compatibility.
     * A Controller instance is always the coordinator of it's group.
     */
    public function isCoordinator(): bool
    {
        return true;
    }


    /**
     * Get the uuid of this speaker.
     */
    public function getUuid(): string
    {
        return $this->speaker->getUuid();
    }


    /**
     * Get the current volume of this speaker.
     */
    public function getVolume(): int
    {
        return $this->speaker->getVolume();
    }


    /**
     * Set the volume of all the speakers this controller manages.
     *
     * @param int $volume The amount to set the volume to between 0 and 100
     *
     * @return $this
     */
    public function setVolume(int $volume): SpeakerInterface
    {
        $speakers = $this->getSpeakers();
        foreach ($speakers as $speaker) {
            $speaker->setVolume($volume);
        }

        return $this;
    }


    /**
     * Adjust the volume of all the speakers this controller manages.
     *
     * @param int $adjust The amount to adjust by between -100 and 100
     *
     * @return $this
     */
    public function adjustVolume(int $adjust): SpeakerInterface
    {
        $speakers = $this->getSpeakers();
        foreach ($speakers as $speaker) {
            $speaker->adjustVolume($adjust);
        }

        return $this;
    }


    /**
     * Check if this speaker is currently muted.
     */
    public function isMuted(): bool
    {
        return $this->speaker->isMuted();
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
        $this->speaker->mute($mute);
        return $this;
    }


    /**
     * Unmute this speaker.
     *
     * @return $this
     */
    public function unmute(): SpeakerInterface
    {
        $this->speaker->unmute();
        return $this;
    }


    /**
     * Check whether the indicator light is on or not.
     */
    public function getIndicator(): bool
    {
        return $this->speaker->getIndicator();
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
        $this->speaker->setIndicator($on);
        return $this;
    }


    /**
     * Get the treble equalisation level.
     */
    public function getTreble(): int
    {
        return $this->speaker->getTreble();
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
        $this->speaker->setTreble($treble);
        return $this;
    }


    /**
     * Get the bass equalisation level.
     */
    public function getBass(): int
    {
        return $this->speaker->getBass();
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
        $this->speaker->setBass($bass);
        return $this;
    }


    /**
     * Check whether loudness normalisation is on or not.
     */
    public function getLoudness(): bool
    {
        return $this->speaker->getLoudness();
    }


    /**
     * Set whether loudness normalisation is on or not.
     *
     * @param bool $on Whether loudness should be on or not
     *
     * @return $this
     */
    public function setLoudness(bool $on): SpeakerInterface
    {
        $this->speaker->setLoudness($on);
        return $this;
    }
}
