<?php

namespace Sonos\MediaServer\ContentDirectory;

use const SOAP_ACTOR_NONE;
use const SOAP_ENC_OBJECT;
use const SOAP_ENCODED;
use SoapParam;

final class Control
{

    public function browse($params)
    {
        echo $params->BrowseFlag . "\n";
        echo $params->StartingIndex . "\n";
        echo $params->RequestedCount . "\n";
        echo $params->Filter . "\n";
        echo $params->ObjectID . "\n";
        echo $params->InstanceID . "\n";
    }


    public function GetZoneGroupAttributes(string $instanceID)
    {
        return [
            "CurrentZoneGroupName" => "",
            "CurrentZoneGroupID" => "RINCON_B8E9375BC89E01400:635",
            "CurrentZonePlayerUUIDsInGroup" => "RINCON_5CAAFD472E1C01400,RINCON_5CAAFD0A251401400",
            "CurrentMuseHouseholdId" => "Sonos_P9Ufbz0f2mKYnHAVXZdaVyKLm5.hGpwxvlyS5Nn7axGyatZ",
        ];
    }
}
