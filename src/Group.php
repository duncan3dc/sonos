<?php

namespace duncan3dc\Sonos;

use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\Interfaces\NetworkInterface;
use duncan3dc\Sonos\Interfaces\SpeakerInterface;
use function array_key_exists;
use duncan3dc\Sonos\Interfaces\UriInterface;

final class Group
{
    /** @var NetworkInterface[] */
    private $network;

    /** @var array */
    private $groups = [];

    /** @var SpeakerInterface[] */
    private $speakers = [];


    public function __construct(NetworkInterface $network)
    {
        $this->network = $network;
    }


    /**
     * @param SpeakerInterface $speaker
     *
     * @return $this
     */
    public function addSpeaker(SpeakerInterface $speaker): self
    {
        $controller = $this->getController($speaker);
        $this->addGroup($controller);

        $this->speakers[] = $speaker;

        return $this;
    }


    public function interrupt(UriInterface $track, int $volume = null): self
    {
        /**
         * Ensure the track has been generated.
         * If it's a TextToSpeech then the api call is done lazily when the uri is required.
         * So it's better to do this here, rather than after the controller has been paused.
         */
        $track->getUri();

        foreach ($this->groups as $group => $data) {
            $this->groups[$group]["state"] = $data["controller"]->exportState();
        }

        $this->group();

        $coordinator = $this->getCoordinator();

        # See Controller::interrupt() for this logic
        $coordinator->useQueue()->getQueue()->clear()->addTrack($track);
        $coordinator->setRepeat(false);
        if ($volume !== null) {
            $coordinator->setVolume($volume);
        }
        $coordinator->play();

        sleep(1);
        while ($coordinator->getState() === ControllerInterface::STATE_PLAYING) {
            usleep(500000);
        }

        $this->ungroup();

        foreach ($this->groups as $data) {
            $data["controller"]->restoreState($data["state"]);
        }

        return $this;
    }


    private function addGroup(ControllerInterface $controller)
    {
        $group = $controller->getGroup();
        if (array_key_exists($group, $this->groups)) {
            return;
        }

        $speakers = [];
        foreach ($controller->getSpeakers() as $speaker) {
            $speakers[] = $speaker;
        }

        $this->groups[$group] = [
            "controller" => $controller,
            "speakers" => $speakers,
        ];
    }


    /**
     * @param SpeakerInterface $speaker
     *
     * @return ControllerInterface
     */
    private function getController(SpeakerInterface $speaker): ControllerInterface
    {
        foreach ($this->network->getControllers() as $controller) {
            if ($controller->getGroup() === $speaker->getGroup()) {
                return $controller;
            }
        }
    }


    /**
     * @return SpeakerInterface
     */
    private function getCoordinator(): ?ControllerInterface
    {
        $coordinator = null;

        foreach ($this->speakers as $speaker) {
            if ($coordinator === null) {
                $coordinator = $speaker;
            }

            if ($speaker->isCoordinator()) {
                $coordinator = $speaker;
                break;
            }
        }

        if ($coordinator === null) {
            return null;
        }

        foreach ($this->network->getControllers() as $controller) {
            if ($controller->getGroup() === $coordinator->getGroup()) {
                $controller->removeSpeaker($coordinator);
            }
        }

        return new Controller($coordinator, $this->network);
    }


    /**
     * @return self
     */
    public function group(): self
    {
        $coordinator = $this->getCoordinator();
        if (!$coordinator) {
            return $this;
        }

        echo $coordinator->getRoom() . "is the coordinator\n";

        foreach ($coordinator->getSpeakers() as $current) {
            if ($current->getUuid() === $coordinator->getUuid()) {
                continue;
            }

            $found = false;
            foreach ($this->speakers as $speaker) {
                if ($speaker->getUuid() === $current->getUuid()) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                echo "removing " . $current->getRoom() . " from the coordinator's group\n";
                $coordinator->removeSpeaker($current);
            } else {
                echo $current->getRoom() . " is already part of the coordinator's group\n";
            }
        }

        foreach ($this->speakers as $speaker) {
            if ($speaker->getGroup() === $coordinator->getGroup()) {
                echo $speaker->getRoom() . " is already part of the coordinator's group\n";
                continue;
            }
            $coordinator->addSpeaker($speaker);
        }

        return $this;
    }


    /**
     * @return self
     */
    public function ungroup(): self
    {
        if ($this->groups === []) {
            return $this;
        }

        $coordinator = $this->getCoordinator();

        foreach ($this->groups as $group) {
            if ($group["controller"]->getUuid() === $coordinator->getUuid()) {
                continue;
            }

            $controller = $group["controller"];
            $coordinator->removeSpeaker($controller);
            foreach ($group["speakers"] as $speaker) {
                $controller->addSpeaker($speaker);
            }
        }

        return $this;
    }
}
