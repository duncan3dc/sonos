---
layout: default
title: Text To Speech
permalink: /fun/text-to-speech/
api: Tracks.TextToSpeech
---

<p class="message-info">This feature was added in v1.2.0</p>

The `TextToSpeech` class is powered by an unpublicised Google API that is part of their translation server.

_At the moment the API is limited to messages of 100 characters._

The API also offers a choice of country, which determines the accent of the voice speaking your message:

~~~php
 $track = new TextToSpeech("Testing, testing, 123", $directory);

 $track->setLanguage("fr");
~~~


As with a lot of the methods in this library, it is chainable:

~~~php
 $track = (new TextToSpeech("Hello", $directory))
     ->setLanguage("fr");
~~~
