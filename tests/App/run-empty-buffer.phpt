--TEST--
App run emits through an empty output buffer
--FILE--
<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Joust\App;
use Joust\Handler\NotFound;
use Joust\Response\JsonResponseFactory;
use Joust\Tests\Fixture\TestContainer;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;

$factory = new Psr17Factory();

$app = new App(new TestContainer([
    NotFound::class => new NotFound(new JsonResponseFactory($factory, $factory)),
]));

ob_start();

$app->run(new ServerRequest('GET', '/'));

ob_end_flush();
--EXPECT--
{"title":"Not Found","status":404}
