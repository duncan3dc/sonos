<?php

namespace duncan3dc\Sonos\Exceptions;

/**
 * Provides extra information about upnp exceptions
 */
final class SoapException extends \Exception implements Exception
{
    /**
     * @var \SoapClient $client The SoapClient instance that threw the SoapFault
     */
    private \SoapClient $client;

    public function __construct(\SoapFault $fault, \SoapClient $client)
    {
        $message = $fault->getMessage();
        $code = $fault->getCode();

        if ($message === "UPnPError") {
            /**
             * @var int $code
             * @phpstan-ignore-next-line
             */
            $code = $fault->detail->UPnPError->errorCode ?? 0;
            if ($code) {
                $message .= ": {$code}";
            }
        }

        parent::__construct($message, $code);

        $this->client = $client;
    }

    /**
     * Get the body of the soap request.
     */
    public function getRequest(): string
    {
        return (string) $this->client->__getLastRequest();
    }


    /**
     * Get the body of the soap response.
     */
    public function getResponse(): string
    {
        return (string) $this->client->__getLastResponse();
    }
}
