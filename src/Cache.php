<?php

namespace duncan3dc\Sonos;

use Doctrine\Common\Cache\FilesystemCache;

/**
 * A cache provider.
 */
class Cache extends FilesystemCache
{
    const MINUTE = 60;
    const HOUR = 3600;
    const DAY = 86400;

    public function __construct()
    {
        return parent::__construct(sys_get_temp_dir() . DIRECTORY_SEPARATOR . "sonos");
    }
}
