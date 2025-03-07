<?php

namespace duncan3dc\Sonos\Utils;

use duncan3dc\Dom\DomInterface;
use duncan3dc\Dom\ElementInterface;
use duncan3dc\Sonos\Exceptions\UnexpectedResponseException;

final class Xml
{
    /**
     * Get a tag but throw an exception if it doesn't exist.
     */
    public static function tag(DomInterface $element, string $tagName): ElementInterface
    {
        $tag = $element->getTag($tagName);
        if ($tag === null) {
            throw new UnexpectedResponseException("Missing <{$tagName}> tag");
        }
        return $tag;
    }
}
