<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\Exceptions\SoapException;

/**
 * Allows interaction with the groups of speakers.
 *
 * Although sometimes a Controller is synonymous with a Speaker, when speakers are grouped together only the coordinator can receive events (play/pause/etc)
 */
class Controller extends Speaker
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
     * @var Network $network The network instance this Controller is part of.
     */
    protected $network;


    /**
     * Create a Controller instance from a speaker.
     *
     * The speaker must be a coordinator.
     *
     * @param Speaker $speaker
     */
    public function __construct(Speaker $speaker, Network $network)
    {
        if (!$speaker->isCoordinator()) {
            throw new \InvalidArgumentException("You cannot create a Controller instance from a Speaker that is not the coordinator of it's group");
        }
        $this->ip = $speaker->ip;
        $this->device = $speaker->device;

        $this->network = $network;
        $this->name = $speaker->name;
        $this->room = $speaker->room;
        $this->group = $speaker->getGroup();
        $this->uuid = $speaker->getUuid();
    }


    /**
     * Check if this speaker is the coordinator of it's current group.
     *
     * This method is only here to override the method from the Speaker class.
     * A Controller instance is always the coordinator of it's group.
     *
     * @return boolean
     */
    public function isCoordinator()
    {
        return true;
    }


    /**
     * Get the current state of the group of speakers as the string reported by sonos: PLAYING, PAUSED_PLAYBACK, etc
     *
     * @return string
     */
    public function getStateName()
    {
        $data = $this->soap("AVTransport", "GetTransportInfo");
        return $data["CurrentTransportState"];
    }


    /**
     * Get the current state of the group of speakers.
     *
     * @return int One of the class STATE_ constants
     */
    public function getState()
    {
        $name = $this->getStateName();
        switch ($name) {
            case "STOPPED":
                return self::STATE_STOPPED;
            case "PLAYING":
                return self::STATE_PLAYING;
            case "PAUSED_PLAYBACK":
                return self::STATE_PAUSED;
            case "TRANSITIONING":
                return self::STATE_TRANSITIONING;
        }
        return self::STATE_UNKNOWN;
    }


    /**
     * Get attributes about the currently active track in the queue.
     *
     * @return State Track data containing the following elements
     */
    public function getStateDetails()
    {
        $data = $this->soap("AVTransport", "GetPositionInfo");

        if (!$data["TrackMetaData"]) {
            return new State;
        }

        $parser = new XmlParser($data["TrackMetaData"]);
        $state = State::createFromXml($parser, $this);

        if ((string) $parser->getTag("streamContent")) {
            $info = $this->soap("AVTransport", "GetMediaInfo");
            if (!$state->stream = (string) (new XmlParser($info["CurrentURIMetaData"]))->getTag("title")) {
                $state->stream = (string) $parser->getTag("title");
            }
        }

        $state->queueNumber = (int) $data["Track"];
        $state->duration = $data["TrackDuration"];
        $state->position = $data["RelTime"];

        # If we have a queue number, it'll be one-based, rather than zero-based, so convert it
        if ($state->queueNumber > 0) {
            $state->queueNumber--;
        }

        return $state;
    }


    /**
     * Set the state of the group.
     *
     * @param int $state One of the class STATE_ constants
     *
     * @return static
     */
    public function setState($state)
    {
        switch ($state) {
            case self::STATE_PLAYING:
                return $this->play();
            case self::STATE_PAUSED:
                return $this->pause();
            case self::STATE_STOPPED;
                return $this->pause();
        }
        throw new \InvalidArgumentException("Unknown state (" . $state . ")");
    }


    /**
     * Start playing the active music for this group.
     *
     * @return static
     */
    public function play()
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
     * @return static
     */
    public function pause()
    {
        $this->soap("AVTransport", "Pause");

        return $this;
    }


    /**
     * Skip to the next track in the current queue.
     *
     * @return static
     */
    public function next()
    {
        $this->soap("AVTransport", "Next");

        return $this;
    }


    /**
     * Skip back to the previous track in the current queue.
     *
     * @return static
     */
    public function previous()
    {
        $this->soap("AVTransport", "Previous");

        return $this;
    }


    /**
     * Skip to the specific track in the current queue.
     *
     * @param int $position The zero-based position of the track to skip to
     *
     * @return static
     */
    public function selectTrack($position)
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
     * @param int $seconds The number of seconds to position to in the track
     *
     * @return static
     */
    public function seek($seconds)
    {
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;

        $this->soap("AVTransport", "Seek", [
            "Unit"      =>  "REL_TIME",
            "Target"    =>  sprintf("%02s:%02s:%02s", $hours, $minutes, $seconds),
        ]);

        return $this;
    }


    /**
     * Get the speakers that are in the group of this controller.
     *
     * @return Speaker[]
     */
    public function getSpeakers()
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
     * @param Speaker $speaker The speaker to add to the group
     *
     * @return static
     */
    public function addSpeaker(Speaker $speaker)
    {
        if ($speaker->getUuid() === $this->getUuid()) {
            return $this;
        }
        $speaker->soap("AVTransport", "SetAVTransportURI", [
            "CurrentURI"            =>  "x-rincon:" . $this->getUuid(),
            "CurrentURIMetaData"    =>  "",
        ]);

        $this->network->clearTopology();

        return $this;
    }


    /**
     * Removes the specified speaker from the group of this Controller.
     *
     * @param Speaker $speaker The speaker to remove from the group
     *
     * @return static
     */
    public function removeSpeaker(Speaker $speaker)
    {
        $speaker->soap("AVTransport", "BecomeCoordinatorOfStandaloneGroup");

        $this->network->clearTopology();

        return $this;
    }


    /**
     * Set the current volume of all the speakers controlled by this Controller.
     *
     * @param int $volume An amount between 0 and 100
     *
     * @return static
     */
    public function setVolume($volume)
    {
        $speakers = $this->getSpeakers();
        foreach ($speakers as $speaker) {
            $speaker->setVolume($volume);
        }

        return $this;
    }


    /**
     * Adjust the volume of all the speakers controlled by this Controller.
     *
     * @param int $adjust A relative amount between -100 and 100
     *
     * @return static
     */
    public function adjustVolume($adjust)
    {
        $speakers = $this->getSpeakers();
        foreach ($speakers as $speaker) {
            $speaker->adjustVolume($adjust);
        }

        return $this;
    }


    /**
     * Get the current play mode settings.
     *
     * @return array An array with 2 boolean elements (shuffle and repeat)
     */
    public function getMode()
    {
        $data = $this->soap("AVTransport", "GetTransportSettings");
        return Helper::getMode($data["PlayMode"]);
    }


    /**
     * Set the current play mode settings.
     *
     * @param array $options An array with 2 boolean elements (shuffle and repeat)
     *
     * @return static
     */
    public function setMode(array $options)
    {
        $this->soap("AVTransport", "SetPlayMode", [
            "NewPlayMode"   =>  Helper::setMode($options),
        ]);

        return $this;
    }


    /**
     * Check if repeat is currently active.
     *
     * @return boolean
     */
    public function getRepeat()
    {
        $mode = $this->getMode();
        return $mode["repeat"];
    }


    /**
     * Turn repeat mode on or off.
     *
     * @param boolean $repeat Whether repeat should be on or not
     *
     * @return static
     */
    public function setRepeat($repeat)
    {
        $repeat = (boolean) $repeat;

        $mode = $this->getMode();
        if ($mode["repeat"] === $repeat) {
            return $this;
        }

        $mode["repeat"] = $repeat;
        $this->setMode($mode);

        return $this;
    }


    /**
     * Check if shuffle is currently active.
     *
     * @return boolean
     */
    public function getShuffle()
    {
        $mode = $this->getMode();
        return $mode["shuffle"];
    }


    /**
     * Turn shuffle mode on or off.
     *
     * @param boolean $shuffle Whether shuffle should be on or not
     *
     * @return static
     */
    public function setShuffle($shuffle)
    {
        $shuffle = (boolean) $shuffle;

        $mode = $this->getMode();
        if ($mode["shuffle"] === $shuffle) {
            return $this;
        }

        $mode["shuffle"] = $shuffle;
        $this->setMode($mode);

        return $this;
    }


    /**
     * Check if crossfade is currently active.
     *
     * @return boolean
     */
    public function getCrossfade()
    {
        return (boolean) $this->soap("AVTransport", "GetCrossfadeMode");
    }


    /**
     * Turn crossfade on or off.
     *
     * @param boolean $crossfade Whether crossfade should be on or not
     *
     * @return static
     */
    public function setCrossfade($crossfade)
    {
        $data = $this->soap("AVTransport", "SetCrossfadeMode", [
            "CrossfadeMode" =>  (boolean) $crossfade,
        ]);

        return $this;
    }


    /**
     * Get the queue for this controller.
     *
     * @return Queue
     */
    public function getQueue()
    {
        return new Queue($this);
    }
}
