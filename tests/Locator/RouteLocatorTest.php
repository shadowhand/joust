<?php

declare(strict_types=1);

namespace Joust\Tests\Locator;

use Joust\Locator\RouteLocator;
use Joust\Route;
use Joust\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psl\Exception\InvariantViolationException;

use function Psl\Vec\map;
use function Psl\Vec\sort;
use function Psl\Vec\values;

#[CoversClass(RouteLocator::class)]
final class RouteLocatorTest extends TestCase
{
    private const string DIRECTORY = __DIR__ . '/../Fixture/Routes';
    private const string EMPTY_DIRECTORY = __DIR__ . '/../Fixture/Empty';

    public function testItCollectsRoutesFromHandlerClassesOnly(): void
    {
        $routes = map(
            values(new RouteLocator(self::DIRECTORY)),
            static fn(Route $route): string => "{$route->method->value} {$route->pattern->path}",
        );

        $this->assertSame(['DELETE /users/:id', 'GET /users', 'GET /users/:id'], sort($routes));
    }

    public function testItYieldsNothingForAnEmptyDirectory(): void
    {
        $this->assertSame([], values(new RouteLocator(self::EMPTY_DIRECTORY)));
    }

    public function testItRejectsAnUnreadableDirectory(): void
    {
        $this->expectException(InvariantViolationException::class);

        new RouteLocator(self::DIRECTORY . '/missing');
    }
}
