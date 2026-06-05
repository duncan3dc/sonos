<?php

namespace duncan3dc\Sonos\Interfaces\Utils;

use Psr\Log\LoggerInterface;

interface SocketInterface
{
    /**
     * Send out the multicast discover request.
     */
    public function request(): string;
}
