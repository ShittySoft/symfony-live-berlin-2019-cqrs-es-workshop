#!/usr/bin/env php
<?php

namespace Building\App;

use Building\Domain\Aggregate\Building;
use Interop\Container\ContainerInterface;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream\StreamName;

(static function () {
    /** @var ContainerInterface $dic */
    $dic = require __DIR__ . '/../container.php';

    $eventStore = $dic->get(EventStore::class);

    /** @var AggregateChanged[] $history */
    $history = $eventStore->loadEventsByMetadataFrom(new StreamName('event_stream'), [
        'aggregate_type' => Building::class,
    ]);

    $usersInBuildings = [];

    foreach ($history as $event) {
        // ... ???
    }

    \array_walk($usersInBuildings, static function (array $users, string $buildingId) {
        \file_put_contents(
            __DIR__ . '/../public/users-' . $buildingId . '.json',
            json_encode(array_keys($users))
        );
    });
})();
