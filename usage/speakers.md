---
layout: default
title: Speakers
permalink: /usage/speakers/
api: Speaker
---

Most actions related to speakers take place on [Controllers](../../controllers/play-some-music/), but there are a few that should be handled via speakers.

Get some information about a speaker:

~~~php
 # Get the IP address of the speaker
 $speaker->ip;

 # Get the "Friendly" name reported by the speaker (spoiler: it's not that friendly)
 $speaker->name;

 # Get the room name assigned to this speaker
 $speaker->room;
~~~


Manage the volume of a speaker:

~~~php
 if ($speaker->getVolume() > 50) {
     $speaker->setVolume(50);
 }

 $speaker->adjustVolume(10);
 $speaker->adjustVolume(-10);
~~~


Mute a speaker:

~~~php
 $speaker->mute();

 if ($speaker->isMuted()) {
     $speaker->unmute();
 }
~~~


Manage the equalisation of a speaker:

~~~php
 if ($speaker->getTreble() > -5) {
     $speaker->setTreble(-5);
 }
 if ($speaker->getBass() < 5) {
     $speaker->setBass(5);
 }

 if (!$speaker->getLoudness()) {
     $speaker->setLoudness(true);
 }
~~~

<p class="message-info">The equalisation methods were added in v1.2.0</p>
