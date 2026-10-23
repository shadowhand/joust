--TEST--
App run fails when output has already been buffered
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
use Psl\Exception\InvariantViolationException;

$factory = new Psr17Factory();

$app = new App(new TestContainer([
    NotFound::class => new NotFound(new JsonResponseFactory($factory, $factory)),
]));

$message = '';

ob_start();

echo 'early';

try {
    $app->run(new ServerRequest('GET', '/'));
} catch (InvariantViolationException $exception) {
    $message = $exception->getMessage();
}

ob_end_clean();

echo $message;
--EXPECT--
Cannot emit response, output has already been buffered
