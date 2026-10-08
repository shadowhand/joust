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
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

use function Psl\IO\write;

require __DIR__ . '/../vendor/autoload.php';

$factory = new Psr17Factory();
$request = new ServerRequestCreator($factory, $factory, $factory, $factory)->fromGlobals();
$response = TodoApp::create()->handle($request);

http_response_code($response->getStatusCode());

foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header("{$name}: {$value}", replace: false);
    }
}

write((string) $response->getBody());
