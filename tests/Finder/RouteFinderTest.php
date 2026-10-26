<?php

declare(strict_types=1);

namespace Joust\Tests\Finder;

use Joust\Finder\RouteFinder;
use Joust\Finder\RouteFinderSettings;
use Joust\Route;
use Joust\Tests\Fixture\Routes\GetUsers;
use Joust\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psl\Exception\InvariantViolationException;

use function Psl\Vec\map;
use function Psl\Vec\sort;
use function Psl\Vec\values;

#[CoversClass(RouteFinder::class)]
final class RouteFinderTest extends TestCase
{
    private const string DIRECTORY = __DIR__ . '/../Fixture/Routes';
    private const string EMPTY_DIRECTORY = __DIR__ . '/../Fixture/Empty';

    public function testItCollectsRoutesFromHandlerClassesOnly(): void
    {
        $routes = map(
            values(new RouteFinder(new RouteFinderSettings(self::DIRECTORY))),
            static fn(Route $route): string => "{$route->method->value} {$route->pattern->path}",
        );

        $this->assertSame(['DELETE /users/:id', 'GET /users', 'GET /users/:id'], sort($routes));
    }

    public function testItYieldsNothingForAnEmptyDirectory(): void
    {
        $this->assertSame([], values(new RouteFinder(new RouteFinderSettings(self::EMPTY_DIRECTORY))));
    }

    public function testItRejectsAnUnreadableDirectory(): void
    {
        $this->expectException(InvariantViolationException::class);

        values(new RouteFinder(new RouteFinderSettings(self::DIRECTORY . '/missing')));
    }

    public function testItAppliesTheConfiguredBaseUrl(): void
    {
        $routes = values(new RouteFinder(new RouteFinderSettings(self::DIRECTORY, 'https://example.com')));

        foreach ($routes as $route) {
            if ($route->handler->name !== GetUsers::class) {
                continue;
            }

            $this->assertNotNull($route->pattern->extract('https://example.com/users'));
            $this->assertNull($route->pattern->extract('https://other.example.com/users'));

            return;
        }

        $this->fail('GetUsers route not found.');
    }
}
