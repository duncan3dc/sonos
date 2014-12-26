---
layout: default
title: Play Some Music
page-title: Start the party!
permalink: /controllers/play-some-music/
api: Controller
---


You can start your whole network playing music:

~~~php
 $controllers = $sonos->getControllers();
 foreach ($controllers as $controller) {
     if ($controller->getState() !== Controller::STATE_PLAYING) {
         $controller->play();
     }
 }
~~~


Or check on your networks current status:

~~~php
 $controllers = $sonos->getControllers();
 foreach ($controllers as $controller) {
     echo "{$controller->room} = {$controller->getStateName()}\n";
 }
~~~


As well as a bunch of other stuff:

~~~php
 # I'm bored of this track
 $controller->next();

 # Oh wait, that was my favourite song
 $controller->previous();

 # Shush a minute
 $controller->pause();
~~~


See the <a href='../../usage/tracks/'>Tracks documentation</a> for info on adding tracks to your controller's queue.
