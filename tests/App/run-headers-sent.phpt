--TEST--
App run fails when headers were already sent
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
use Psl\Exception\InvariantViolationException;

$factory = new Psr17Factory();

$app = new App(new TestContainer([
    NotFoundHandler::class => new NotFoundHandler(new JsonResponseFactory($factory, $factory)),
]));

echo "early\n";

try {
    $app->run(new ServerRequest('GET', '/'));
} catch (InvariantViolationException $exception) {
    echo $exception->getMessage();
}
--EXPECT--
early
Cannot emit response, headers already sent
