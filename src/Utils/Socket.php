<?php

namespace duncan3dc\Sonos\Utils;

use duncan3dc\Sonos\Exceptions\NetworkException;
use duncan3dc\Sonos\Interfaces\Utils\SocketInterface;
use Psr\Log\LoggerInterface;
use function getprotobyname;
use function socket_create;
use function socket_last_error;
use function socket_recvfrom;
use function socket_select;
use function socket_sendto;
use function socket_set_option;
use function socket_strerror;
use function strlen;

final class Socket implements SocketInterface
{
    /**
     * @var string|int|null $networkInterface The network interface to use for SSDP discovery.
     */
    private $networkInterface;

    /**
     * @var string $multicastAddress The multicast address to use for SSDP discovery.
     */
    private $multicastAddress;

    /**
     * @var LoggerInterface $logger The logging object.
     */
    private $logger;


    /**
     * Create a new instance.
     *
     * @param string|int|null $networkInterface The interface to use
     * @param string $multicastAddress The address to use
     * @param LoggerInterface $logger A logging object
     */
    public function __construct($networkInterface, string $multicastAddress, LoggerInterface $logger)
    {
        $this->networkInterface = $networkInterface;
        $this->multicastAddress = $multicastAddress;
        $this->logger = $logger;
    }


    /**
     * Send out the multicast discover request.
     *
     * @return string
     * @throws NetworkException
     */
    public function request(): string
    {
        $sock = socket_create(\AF_INET, \SOCK_DGRAM, \SOL_UDP);
        if ($sock === false) {
            throw new NetworkException(socket_strerror(socket_last_error()));
        }

        $level = (int) getprotobyname("ip");

        socket_set_option($sock, $level, \IP_MULTICAST_TTL, 2);

        if ($this->networkInterface !== null) {
            socket_set_option($sock, $level, \IP_MULTICAST_IF, $this->networkInterface);
        }

        $request = "M-SEARCH * HTTP/1.1\r\n";
        $request .= "HOST: {$this->multicastAddress}:reservedSSDPport\r\n";
        $request .= "MAN: ssdp:discover\r\n";
        $request .= "MX: 1\r\n";
        $request .= "ST: urn:schemas-upnp-org:device:ZonePlayer:1\r\n";

        $this->logger->debug($request);

        socket_sendto($sock, $request, strlen($request), 0, $this->multicastAddress, 1900);

        $read = [$sock];
        $write = [];
        $except = [];
        $name = null;
        $port = null;
        $tmp = "";

        $response = "";
        while (socket_select($read, $write, $except, 1)) {
            socket_recvfrom($sock, $tmp, 2048, 0, $name, $port);
            $response .= $tmp;
        }

        $this->logger->debug($response);

        return $response;
    }
}
