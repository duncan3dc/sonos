<?php

namespace duncan3dc\Sonos\Interfaces;

enum PlayState
{
    /**
     * No music playing, but not paused.
     *
     * This is a rare state, but can be encountered after an upgrade, or if the queue was cleared
     */
    case Stopped;

    /**
     * Currently plating music.
     */
    case Playing;

    /**
     * Music is currently paused.
     */
    case Paused;

    /**
     * The speaker is currently working on either playing or pausing.
     *
     * Check it's state again in a second or two
     */
    case Transitioning;

    /**
     * The speaker is in an unknown state.
     *
     * This should only happen if Sonos introduce a new state that this code has not been updated to handle.
     */
    case Unknown;
}
