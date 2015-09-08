<?php

namespace duncan3dc\Sonos\Tracks;

use duncan3dc\Sonos\Directory;
use duncan3dc\Sonos\Helper;
use duncan3dc\Speaker\Providers\GoogleProvider;
use duncan3dc\Speaker\Providers\ProviderInterface;
use duncan3dc\Speaker\TextToSpeech as TextToSpeechHandler;

/**
 * Convert a string of a text to a spoken word mp3.
 */
class TextToSpeech implements UriInterface
{
    /**
     * @var Directory $directory The directory to store the mp3 in.
     */
    protected $directory;

    /**
     * @var string $text The text to convert.
     */
    protected $text;

    /**
     * @var string $filename The filename of the of the track.
     */
    protected $filename;

    /**
     * @var Provider $provider The text to speech provider.
     */
    protected $provider;

    /**
     * Create a TextToSpeech object.
     *
     * @param string $text The text to convert
     * @param Directory $directory The directory to store the mp3 in.
     */
    public function __construct($text, Directory $directory, ProviderInterface $provider = null)
    {
        if (strlen($text) > 100) {
            throw new \InvalidArgumentException("Only messages under 100 characters are supported");
        }

        $this->directory = $directory;
        $this->text = $text;
        $this->filename = md5($this->text) . ".mp3";

        if ($provider !== null) {
            $this->setProvider($provider);
        }
    }


    public function setProvider(ProviderInterface $provider)
    {
        $this->provider = $provider;

        return $this;
    }


    public function getProvider()
    {
        if ($this->provider === null) {
            $this->provider = new GoogleProvider;
        }

        return $this->provider;
    }


    /**
     * Set the language to use in the google text-to-speech call.
     *
     * @param string $language The language to use (eg 'en')
     *
     * @return static
     */
    public function setLanguage($language)
    {
        $this->getProvider()->setLanguage($language);

        return $this;
    }


    /**
     * Get the URI for this message.
     *
     * If it doesn't already exist on the filesystem then the text-to-speech handler will be called.
     *
     * @return string
     */
    public function getUri()
    {
        if (!$this->directory->has($this->filename)) {
            $provider = $this->getProvider();
            $tts = new TextToSpeechHandler($this->text, $provider);
            $mp3 = $tts->getAudioData();
            $this->directory->write($this->filename, $mp3);
        }

        return "x-file-cifs://" . $this->directory->getSharePath() . "/{$this->filename}";
    }


    /**
     * Get the metadata xml for this message.
     *
     * @return string
     */
    public function getMetaData()
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
