<?php

namespace duncan3dc\SonosTests\Devices;

use duncan3dc\ObjectIntruder\Intruder;
use duncan3dc\Sonos\Devices\Device;
use duncan3dc\SonosTests\MockTest;

class DeviceTest extends MockTest
{
    /**
     * @return iterable<array<mixed>>
     */
    public function modelProvider(): iterable
    {
        $speakers = [
            "S1",
            "S12",
            "S3",
            "S5",
            "S6",
            "S9",
            "S11",
            "S13",
            "S14",
            "S15",
            "S16",
            "S17",
            "S18",
            "S19",
            "S20",
            "S21",
            "S22",
            "S23",
            "S24",
            "S27",
            "S29",
            "S31",
            "S33",
            "S35",
            "S38",
            "ZP80",
            "ZP90",
            "ZP100",
            "ZP120",
        ];
        foreach ($speakers as $model) {
            yield [$model, true];
        }

        $others = [
            "ZB100",
            "",
            0,
            false,
        ];
        foreach ($others as $model) {
            yield [$model, false];
        }
    }
    /**
     * @dataProvider modelProvider
     * @param mixed $model
     */
    public function testIsSpeaker($model, bool $expected): void
    {
        $device = new Intruder(new Device("127.0.0.1"));

        $device->model = $model;

        $this->assertSame($expected, $device->isSpeaker());
    }
}
