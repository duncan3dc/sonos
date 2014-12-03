<?php

namespace duncan3dc\Sonos\Test;

use duncan3dc\Sonos\Controller;
use duncan3dc\Sonos\Network;

class ControllerTest extends MockTest
{

    public function testPlay()
    {
        $device = $this->getDevice();
        $controller = $this->getController($device);

        $device->shouldReceive("soap")->once()->with("AVTransport", "Play", [
            "Speed" =>  1,
        ])->andReturn("PASSTHRU");

        $this->assertSame("PASSTHRU", $controller->play());
    }
}
