---
layout: default
title: Line-In
permalink: /controllers/line-in/
api: Interfaces.ControllerInterface
---

When using speakers that have Line-In support, you can set controllers to play them like so:

```php
$office = $sonos->getSpeakerByRoom("Office");

$bedroom = $sonos->getControllerByRoom("Bedroom");
$bedroom->useLineIn($office)->play();
```

<p class="message-info">Line-In usage is technically a stream, so Controller::isStreaming() will return true.</p>


The `useLineIn()` parameter is optional if you want the controller to play its own Line-In, however this might not have the desired effect if speakers are grouped, as the speaker with the Line-In option might not be the controller, so it's recommended that you always pass a speaker instance.

```php
$sonos->getControllerByRoom("Bedroom")->useLineIn()->play();
```
