<?php

namespace duncan3dc\Sonos\Tracks;

use duncan3dc\DomParser\XmlElement;
use duncan3dc\Sonos\Interfaces\ControllerInterface;
use duncan3dc\Sonos\Interfaces\TrackInterface;
use duncan3dc\Sonos\Interfaces\Tracks\FactoryInterface;

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
     * @return string
     */
    private function guessTrackClass(string $uri): string
    {
        $classes = [
            Spotify::class,
            SpotifyPlaylist::class,
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

        return Track::class;
    }


    /**
     * Create a new Track instance from a URI.
     *
     * @param string $uri The URI of the track
     *
     * @return TrackInterface
     */
    public function createFromUri(string $uri): TrackInterface
    {
        $class = $this->guessTrackClass($uri);

        return new $class($uri);
    }


    /**
     * Create a new Track instance from a URI.
     *
     * @param XmlElement $xml The xml element representing the track meta data.
     *
     * @return TrackInterface
     */
    public function createFromXml(XmlElement $xml): TrackInterface
    {
        $uri = (string) $xml->getTag("res");
        $class = $this->guessTrackClass($uri);

        return $class::createFromXml($xml, $this->controller);
    }
}
