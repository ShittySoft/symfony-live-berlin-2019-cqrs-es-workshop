#!/usr/bin/env php
<?php

namespace Building\App;

use Interop\Container\ContainerInterface;

(static function () {
    /** @var ContainerInterface $dic */
    (require __DIR__ . '/../container.php')
        ->get('project-checked-in-users')();
})();
