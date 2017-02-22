<?php

function Browse($params)
{
    echo $params->BrowseFlag . "\n";
    echo $params->StartingIndex . "\n";
    echo $params->RequestedCount . "\n";
    echo $params->Filter . "\n";
    echo $params->ObjectID . "\n";
    echo $params->InstanceID . "\n";
}
