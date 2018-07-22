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

Once it's been created, it can be used exactly like any other [track](../../usage/tracks/), or as an [announcement](../announcements/).
