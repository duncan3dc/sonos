<?php

namespace duncan3dc\Sonos\Tracks;

use duncan3dc\DomParser\XmlElement;
use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\Interfaces\TrackInterface;
use duncan3dc\Sonos\Interfaces\Tracks\FactoryInterface;
use duncan3dc\Sonos\Interfaces\UriInterface;
use duncan3dc\Sonos\Playlist;

/**
 * Factory for creating Track instances.
 */
final class Factory implements FactoryInterface
{
    /**
     * @var ControllerInterface $controller A Controller instance to communicate with.
     */
    private $controller;


    /**
     * Create an instance of the Factory class.
     *
     * @param ControllerInterface $controller A Controller instance to communicate with
     */
    public function __construct(ControllerInterface $controller)
    {
        $this->controller = $controller;
    }


    /**
     * Get the name of the Track class that represents a URI.
     *
     * @param string $uri The URI of the track
     *
     * @return string|UriInterface
     */
    private function guessTrackClass(string $uri): string
    {
        $classes = [
            Spotify::class,
            Google::class,
            GoogleUnlimited::class,
            Deezer::class,
            Stream::class,
        ];
        foreach ($classes as $class) {
            if (substr($uri, 0, strlen($class::PREFIX)) === $class::PREFIX) {
                return $class;
            }
        }

        if (substr($uri, 0, 38) === "file:///jffs/settings/savedqueues.rsq#") {
            $id = (int) substr($uri, 38);
            if ($id > 0) {
                return new Playlist("SQ:{$id}", $this->controller);
            }
        }

        return Track::class;
    }


    /**
     * Create a new Track instance from a URI.
     *
     * @param string $uri The URI of the track
     *
     * @return UriInterface
     */
    public function createFromUri(string $uri): TrackInterface
    {
        $class = $this->guessTrackClass($uri);

        if (is_object($class)) {
            return $class;
        }

        return new $class($uri);
    }


    /**
     * Create a new Track instance from a URI.
     *
     * @param XmlElement $xml The xml element representing the track meta data.
     *
     * @return UriInterface
     */
    public function createFromXml(XmlElement $xml): TrackInterface
    {
        $uri = (string) $xml->getTag("res");
        $class = $this->guessTrackClass($uri);

        if (is_object($class)) {
            return $class;
        }

        return $class::createFromXml($xml, $this->controller);
    }
}
