<?php

declare(strict_types=1);

namespace Joust\Demo;

use Errata\Middleware\ProblemMiddleware;
use Joust\Cache\RouteCollector;
use Joust\Handler\RoutingHandler;
use Joust\Response\JsonResponseFactory;
use Joust\RouteList;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Server\RequestHandlerInterface;

use function Psl\Vec\values;

final readonly class TodoApp
{
    public static function create(): RequestHandlerInterface
    {
        $factory = new Psr17Factory();
        $store = new TodoStore();
        $json = new JsonResponseFactory($factory, $factory);
        $container = new Container([
            JsonResponseFactory::class => $json,
            Routes\Home::class => new Routes\Home($factory, $factory),
            Routes\ListTodos::class => new Routes\ListTodos($store, $json),
            Routes\GetTodo::class => new Routes\GetTodo($store, $json),
            Routes\CreateTodo::class => new Routes\CreateTodo($store, $json),
            Routes\CompleteTodo::class => new Routes\CompleteTodo($store, $json),
            Routes\Crash::class => new Routes\Crash(),
        ]);

        $routes = new RouteList(...values(new RouteCollector()->in(__DIR__ . '/Routes')));

        return new Pipeline(
            [new ProblemMiddleware($factory, $factory), new Middleware\ParseJsonBody($json)],
            new RoutingHandler($container, $json, $routes),
        );
    }
}
