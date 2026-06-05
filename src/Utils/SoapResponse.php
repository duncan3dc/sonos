<?php

namespace duncan3dc\Sonos\Utils;

use duncan3dc\Sonos\Exceptions\UnexpectedValueException;

use function gettype;
use function is_array;
use function is_string;

/**
 * Wrapper around the response from \SoapClient#__soapCall()
 * @internal
 */
final class SoapResponse
{
    /** @var array<string, string>|string|null */
    private array|string|null $data;


    public function __construct(mixed $data)
    {
        if (is_array($data)) {
            /** @var array<string, string> $data */
            $this->data = $data;
            return;
        }

        if (is_string($data)) {
            $this->data = $data;
            return;
        }

        if ($data === null) {
            $this->data = $data;
            return;
        }

        throw new UnexpectedValueException("Unexpected SOAP response type: " . gettype($data));
    }


    /**
     * @return array<string, string>
     */
    public function getArray(): array
    {
        if (!is_array($this->data)) {
            throw new UnexpectedValueException("Unexpected SOAP response type: " . gettype($this->data));
        }

        return $this->data;
    }


    public function getString(): string
    {
        if (is_array($this->data)) {
            throw new UnexpectedValueException("Unexpected SOAP response type: " . gettype($this->data));
        }

        return (string) $this->data;
    }


    public function getInteger(): int
    {
        if (is_array($this->data)) {
            throw new UnexpectedValueException("Unexpected SOAP response type: " . gettype($this->data));
        }

        return (int) $this->data;
    }


    public function getBoolean(): bool
    {
        if (is_array($this->data)) {
            throw new UnexpectedValueException("Unexpected SOAP response type: " . gettype($this->data));
        }

        return (bool) $this->data;
    }
}
