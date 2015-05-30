---
layout: default
title: Text To Speech
permalink: /fun/text-to-speech/
api: Tracks.TextToSpeech
---

<p class="message-info">This feature was added in v1.3.0</p>

The `TextToSpeech` class is powered by [duncan3dc/speaker](https://github.com/duncan3dc/speaker).

~~~php
use duncan3dc\Speaker\Providers\GoogleProvider;

$track = new TextToSpeech("Testing, testing, 123", $directory, new GoogleProvider);
~~~

To retain backwards compatibility, the provider parameter is optional, and will use `GoogleProvider` by default. It can also be overridden like so:

~~~php
use duncan3dc\Speaker\Providers\VoxygenProvider;

$track->setProvider(new VoxygenProvider);
~~~
