<?php

namespace duncan3dc\SonosTests;

use duncan3dc\ObjectIntruder\Intruder;
use duncan3dc\Sonos\Device;

class DeviceTest extends MockTest
{

    public function modelProvider()
    {
        $models = [
            "S1"    =>  true,
            "S12"   =>  true,
            "S3"    =>  true,
            "S5"    =>  true,
            "S6"    =>  true,
            "S9"    =>  true,
            "ZP80"  =>  true,
            "ZP90"  =>  true,
            "ZP100" =>  true,
            "ZP120" =>  true,
            "ZB100" =>  false,
            ""      =>  false,
            0       =>  false,
            null    =>  false,
            false   =>  false,
        ];
        foreach ($models as $model => $isSpeaker) {
            yield [$model, $isSpeaker];
        }
    }
    /**
     * @dataProvider modelProvider
     */
    public function testIsSpeaker($model, $expected)
    {
        $device = new Intruder(new Device("127.0.0.1"));

        $device->model = $model;

        $this->assertSame($expected, $device->isSpeaker());
    }
}
