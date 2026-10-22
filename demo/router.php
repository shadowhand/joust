<?php

declare(strict_types=1);

/**
 * Front controller for the joust todo demo.
 *
 * Run it with PHP's built-in server:
 *
 *     php -S localhost:8080 demo/router.php
 */

use Joust\Demo\TodoApp;

require __DIR__ . '/../vendor/autoload.php';

TodoApp::create()->run();
