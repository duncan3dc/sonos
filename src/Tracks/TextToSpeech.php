<?php

namespace duncan3dc\Sonos\Tracks;

use duncan3dc\Sonos\Helper;
use duncan3dc\Sonos\Interfaces\UriInterface;
use duncan3dc\Sonos\Interfaces\Utils\DirectoryInterface;
use duncan3dc\Speaker\Providers\ProviderInterface;
use duncan3dc\Speaker\TextToSpeech as TextToSpeechHandler;

/**
 * Convert a string of a text to spoken word audio.
 */
final class TextToSpeech implements UriInterface
{
    /**
     * @var DirectoryInterface The directory to store the audio file in.
     */
    private $directory;

    /**
     * @var string The text to convert.
     */
    private $text;

    /**
     * @var ProviderInterface The text to speech provider.
     */
    private $provider;

    /**
     * Create a TextToSpeech object.
     *
     * @param string $text The text to convert
     * @param DirectoryInterface $directory The directory to store the audio file in
     * @param ProviderInterface $provider The tts provider to use
     */
    public function __construct(string $text, DirectoryInterface $directory, ProviderInterface $provider)
    {
        $this->text = $text;
        $this->directory = $directory;
        $this->provider = $provider;
    }


    /**
     * Get the URI for this message.
     *
     * If it doesn't already exist on the filesystem then the text-to-speech handler will be called.
     *
     * @return string
     */
    public function getUri(): string
    {
        $tts = new TextToSpeechHandler($this->text, $this->provider);

        $filename = $tts->generateFilename();

        if (!$this->directory->has($filename)) {
            $data = $tts->getAudioData();
            $this->directory->write($filename, $data);
        }

        return "x-file-cifs://" . $this->directory->getSharePath() . "/{$filename}";
    }


    /**
     * Get the metadata xml for this message.
     *
     * @return string
     */
    public function getMetaData(): string
    {
        return Helper::createMetaDataXml("-1", "-1", [
            "res"               =>  $this->getUri(),
            "upnp:albumArtURI"  =>  "",
            "dc:title"          =>  $this->text,
            "upnp:class"        =>  "object.item.audioItem.musicTrack",
            "dc:creator"        =>  "Google",
            "upnp:album"        =>  "Text To Speech",
        ]);
    }
}
