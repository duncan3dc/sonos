<?php

namespace duncan3dc\Sonos\Exceptions;

/**
 * Provides extra information about upnp exceptions
 */
class SoapException extends SonosException
{
    /**
     * @param \SoapClient $client The SoapClient instance that threw the SoapFault
     */
    protected $client;

    /**
     * Create a new SoapException.
     */
    public function __construct(\SoapFault $fault, \SoapClient $client)
    {
        $message = $fault->getMessage();
        $code = $fault->getCode();

        if ($message === "UPnPError") {
            $code = $fault->detail->UPnPError->errorCode;
            $message .= ": {$code}";
        }

        parent::__construct($message, $code);

        $this->client = $client;
    }

    /**
     * Get the body of the soap request.
     *
     * @return string
     */
    public function getRequest(): string
    {
        return $this->client->__getLastRequest();
    }


    /**
     * Get the body of the soap response.
     *
     * @return string
     */
    public function getResponse(): string
    {
        return $this->client->__getLastResponse();
    }
}
