<?php

namespace Sonos\MediaRenderer\AVTransport;

final class Control
{

    public function AddMultipleURIsToQueue($input)
    {
        return [
            "NewUpdateID" => $input["UpdateID"] + 1,
        ];
    }
}
