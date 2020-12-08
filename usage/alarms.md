---
layout: default
title: Alarms
permalink: /usage/alarms/
api: Interfaces.AlarmInterface
---

You can get existing alarms from the [Network class](../getting-started/#alarms) and delete one like so:

```php
$alarm->delete();
```


Get the details of the alarm:

```php
echo "Alarm ID: " . $alarm->getId() . "\n";
echo "Time: " . $alarm->getTime() . "\n";
echo "Duration: " . $alarm->getDuration() . "\n";
echo "Room: " . $alarm->getSpeaker()->getRoom() . "\n";
echo "Volume: " . $alarm->getVolume() . "\n";
echo "Frequency: " . $alarm->getFrequencyDescription() . "\n";
```


Update the details of the alarm:

```php
$alarm
    ->setTime(Time::parse("15:45"));
    ->setDuration(15);
    ->setVolume(15);
    ->setRepeat(false)
    ->setShuffle(true)
    ->setSpeaker($sonos->getSpeakerByRoom("Living Room"));
```


Check if an alarm is active on a particular day:

```php
if ($alarm->onMonday()) {
    echo "Alarm active on Mondays\n";
}

if ($alarm->daily()) {
    echo "Alarm active every day\n";
}
```


Update an alarm to be active or not on a particular day:

```php
$alarm->onMonday(true);
$alarm->onTuesday(false);
$alarm->onWednesday(false);
```


Some alarms are configured to only go off once and never again:

```php
if (!$alarm->once()) {
    $alarm->once(true);
}
echo "This alarm runs once only\n";
```


Some alarms are configured to go off every day:

```php
if (!$alarm->daily()) {
    $alarm->daily(true);
}
echo "This alarm runs every day\n";
```


Check whether the alarm is active or not:

```php
if ($alarm->isActive()) {
    $alarm->deactivate();
} else {
    $alarm->activate();
}
```


Instead of the day methods available above you can also use bitwise operators:

```php
if ($alarm->getFrequency() & Alarm::MONDAY) {
    echo "Alarm active on Mondays\n";
}

$alarm->setFrequency(Alarm::MONDAY | Alarm::TUESDAY);
```
