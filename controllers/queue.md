---
layout: default
title: Queue
permalink: /controllers/queue/
api: Queue
---

You can get the queue for a controller like so:

~~~php
 $queue = $controller->getQueue();
~~~


The Queue class implements the Countable interface which means you can get the number of tracks by simply counting it:

~~~php
 $numberOfTracks = count($queue);

 # Or call the actual count method
 $numberOfTracks = $queue->count();
~~~


You can empty a queue using the clear method:

~~~php
 $queue->clear();
~~~


Add all the tracks from a playlist to the queue:

~~~php
 $playlist = $sonos->getPlaylistByName("protest the hero");

 $tracks = $playlist->getTracks();
 $queue->addTracks($tracks);
~~~
<p class="message-info">The getTracks() method returns an array of <a href='../tracks/'>Tracks</a>.</p>


Remove tracks from the queue:

~~~php
 $remove = [];
 foreach ($queue->getTracks() as $position => $track) {
     if ($track->artist === "pomegranate tiger") {
         $remove[] = $position;
     }
 }
 if (count($remove) > 0) {
     $queue->removeTracks($remove);
 }
~~~
<p class="message-info">This is done using a single call to removeTracks() because all the positions will be recalculated once a track has been removed, so the other positions would now be invalid. It's also more efficient as we only send one request to the Sonos network instead of many.</p>
