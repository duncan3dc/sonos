<?php

namespace duncan3dc\Sonos\Interfaces\Services;

use duncan3dc\Sonos\Tracks\Stream;

interface RadioInterface
{
    /**
     * Get the favourite radio stations.
     *
     * @return Stream[]
     */
    public function getFavouriteStations(): array;


    /**
     * Get the favourite radio station with the specified name.
     *
     * If no case-sensitive match is found it will return a case-insensitive match.
     *
     * @param string $name The name of the station
     *
     * @return Stream
     */
    public function getFavouriteStation(string $name): Stream;


    /**
     * Get the favourite radio shows.
     *
     * @return Stream[]
     */
    public function getFavouriteShows(): array;


    /**
     * Get the favourite radio show with the specified name.
     *
     * If no case-sensitive match is found it will return a case-insensitive match.
     *
     * @param string $name The name of the show
     *
     * @return Stream
     */
    public function getFavouriteShow(string $name): Stream;
}
