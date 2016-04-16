# Increase the test coverage

# Can we make logging easier? At the moment it seems like you have to pass a logger instance to 3 different objects just to get basic logging











AVTransport
  ReorderTracksInQueue
  ReorderTracksInSavedQueue

Handle stereo pairs better, currently we present them as 2 separate speakers, but most of their actions are paired

What's this mess in Controller.php
        if ((string) $parser->getTag("streamContent")) {
            $info = $this->getMediaInfo();
            $meta = new XmlParser($info["CurrentURIMetaData"]);
            if (!$state->stream = (string) $meta->getTag("title")) {
                $state->setStream = new Stream("", $parser->getTag("title"));
            }
        }
