---
layout: default
title: Speakers
page-title: Join the party!
permalink: /controllers/speakers/
---

You can add speakers to this Controllers group

~~~php
$kitchen = $sonos->getSpeakerByRoom("Kitchen");
$controller->addSpeaker($kitchen);
~~~


I'm trying to sleep...

~~~php
$bedroom = $sonos->getSpeakerByRoom("Bedroom");
$controller->removeSpeaker($bedroom);
~~~
