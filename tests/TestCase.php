<?php

declare(strict_types=1);

namespace Joust\Tests;

use Joust\Method;
use Joust\Route;
use Joust\RouteHandler;
use Joust\RouteMatch;
use Joust\Tests\Fixture\TestHandler;
use League\Uri\UrlPattern;
use League\Uri\UrlPattern\Result;
use League\Uri\UrlPatternBuilder;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * @internal
 */
abstract class TestCase extends PHPUnitTestCase
{
    /**
     * @param class-string<RequestHandlerInterface> $handler
     */
    protected function createRoute(
        Method $method = Method::Get,
        string $pattern = '/',
        string $handler = TestHandler::class,
        ?string $baseUrl = null,
    ): Route {
        $builder = new UrlPatternBuilder(baseUrl: $baseUrl);
        $pattern = $builder->path($pattern)->build();

        return new Route($method, $pattern, new RouteHandler($handler));
    }

    /**
     * @param class-string<RequestHandlerInterface>|null $handler
     */
    protected function createRouteMatch(?string $handler = null, ?Result $result = null): RouteMatch
    {
        if (!$handler) {
            $handler = TestHandler::class;
        }

        if (!$result) {
            $result = new UrlPatternBuilder()
                ->build()
                ->extract('/');

            $this->assertInstanceOf(Result::class, $result);
        }

        return new RouteMatch(new RouteHandler($handler), $result);
    }
}
