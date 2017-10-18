---
layout: default
title: Text To Speech
permalink: /fun/text-to-speech/
api: Tracks.TextToSpeech
---

The `TextToSpeech` class is powered by [duncan3dc/speaker](//github.com/duncan3dc/speaker).

```php
use duncan3dc\Speaker\Providers\GoogleProvider;

$track = new TextToSpeech("Testing, testing, 123", $directory, new GoogleProvider);
```

To retain backwards compatibility, the provider parameter is optional, and will use `GoogleProvider` by default. It can also be overridden like so:

```php
use duncan3dc\Speaker\Providers\PicottsProvider;

$track->setProvider(new PicottsProvider);
```
