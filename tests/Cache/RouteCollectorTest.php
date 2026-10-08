<?php

declare(strict_types=1);

namespace Joust\Tests\Cache;

use Joust\Cache\RouteCollector;
use Joust\Method;
use Joust\Route;
use Joust\Tests\Fixture\Routes\GetUsers;
use Joust\Tests\Fixture\Routes\NotAHandler;
use Joust\Tests\Fixture\Routes\UserActions;
use Joust\Tests\Fixture\TestHandler;
use Joust\Tests\TestCase;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use Psl\Type\Exception\AssertException;

use function Psl\Vec\map;
use function Psl\Vec\sort;
use function Psl\Vec\values;

#[CoversClass(RouteCollector::class)]
final class RouteCollectorTest extends TestCase
{
    private const string DIRECTORY = __DIR__ . '/../Fixture/Routes';

    private const string EMPTY_DIRECTORY = __DIR__ . '/../Fixture/Empty';

    public function testInCollectsRoutesFromHandlers(): void
    {
        $routes = [...new RouteCollector()->in(self::DIRECTORY)];

        $handlers = sort(map($routes, static fn(Route $route): string => $route->handler->name));

        $this->assertSame([GetUsers::class, UserActions::class, UserActions::class], $handlers);
    }

    public function testInSkipsClassesThatAreNotHandlers(): void
    {
        $routes = [...new RouteCollector()->in(self::DIRECTORY)];

        $handlers = map($routes, static fn(Route $route): string => $route->handler->name);

        $this->assertNotContains(NotAHandler::class, $handlers);
    }

    public function testInYieldsNothingWhenDirectoryHasNoHandlers(): void
    {
        $routes = [...new RouteCollector()->in(self::EMPTY_DIRECTORY)];

        $this->assertSame([], $routes);
    }

    public function testOnCollectsEveryRouteAttribute(): void
    {
        $routes = [...new RouteCollector()->on(UserActions::class)];

        $this->assertCount(2, $routes);

        $get = $routes[0] ?? throw new LogicException('Expected a GET route.');
        $delete = $routes[1] ?? throw new LogicException('Expected a DELETE route.');

        $this->assertSame(Method::Get, $get->method);
        $this->assertSame(Method::Delete, $delete->method);
        $this->assertSame('/users/{id}', $get->pattern->path);
        $this->assertSame(UserActions::class, $get->handler->name);
    }

    public function testOnYieldsNothingForHandlerWithoutRouteAttributes(): void
    {
        $routes = [...new RouteCollector()->on(TestHandler::class)];

        $this->assertSame([], $routes);
    }

    public function testOnRejectsClassThatIsNotAHandler(): void
    {
        $this->expectException(AssertException::class);

        values(new RouteCollector()->on(NotAHandler::class));
    }
}
