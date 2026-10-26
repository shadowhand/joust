<?php

declare(strict_types=1);

/**
 * Front controller for the joust todo demo.
 *
 * Run it with PHP's built-in server:
 *
 *     php -S localhost:8081 demo/router.php
 */

use Joust\Demo\TodoApp;
use Nyholm\Psr7\ServerRequest;

require __DIR__ . '/../vendor/autoload.php';

/** Build a PSR-7 request from the built-in server's globals; a real deployment would use its SAPI integration. */
$headers = getallheaders();
$body = file_get_contents('php://input');

$request = new ServerRequest(
    method: $_SERVER['REQUEST_METHOD'] ?? 'GET',
    uri: $_SERVER['REQUEST_URI'] ?? '/',
    headers: false === $headers ? [] : $headers,
    body: false === $body ? null : $body,
    serverParams: $_SERVER,
);

TodoApp::create()->run($request);
