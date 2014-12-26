---
layout: default
title: Volume
page-title: Turn it up!
permalink: /controllers/volume/
---

You can control the volume for [each speaker](../../usage/speakers/), but you can also control it for all speakers in a group here:

~~~php
 $controller->setVolume(30);

 $controller->adjustVolume(-10);

 $controller->adjustVolume(10);
~~~

<p class="message-warning">You can't get the volume of a controller at the moment, because all the speakers could have different volumes.</p>
