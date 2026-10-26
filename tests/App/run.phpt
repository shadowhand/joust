--TEST--
App run emits the response
--FILE--
<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Joust\App;
use Joust\Handler\NotFoundHandler;
use Joust\Response\JsonResponseFactory;
use Joust\Tests\Fixture\TestContainer;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;

$factory = new Psr17Factory();

$app = new App(new TestContainer([
    NotFoundHandler::class => new NotFoundHandler(new JsonResponseFactory($factory, $factory)),
]));

$app->run(new ServerRequest('GET', '/'));
--EXPECT--
{"title":"Not Found","status":404}
