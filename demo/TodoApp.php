<?php

declare(strict_types=1);

namespace Joust\Demo;

use Errata\Middleware\ProblemMiddleware;
use Joust\App;
use Joust\Response\JsonResponseFactory;
use Nyholm\Psr7\Factory\Psr17Factory;

final readonly class TodoApp
{
    public static function create(): App
    {
        $factory = new Psr17Factory();
        $store = new TodoStore();
        $json = new JsonResponseFactory($factory, $factory);
        $container = new Container([
            JsonResponseFactory::class => $json,
            Middleware\ParseJsonBody::class => new Middleware\ParseJsonBody($json),
            Routes\Home::class => new Routes\Home($factory, $factory),
            Routes\ListTodos::class => new Routes\ListTodos($store, $json),
            Routes\GetTodo::class => new Routes\GetTodo($store, $json),
            Routes\CreateTodo::class => new Routes\CreateTodo($store, $json),
            Routes\CompleteTodo::class => new Routes\CompleteTodo($store, $json),
            Routes\Crash::class => new Routes\Crash(),
        ]);

        return new App($container, $factory)->add(new ProblemMiddleware($factory, $factory))->collectRoutes(__DIR__
        . '/Routes');
    }
}
