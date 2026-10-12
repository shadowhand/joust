<?php

declare(strict_types=1);

namespace Joust;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Override;
use Psr\Http\Message\ServerRequestInterface;
use Traversable;

use function Psl\Iter\count;
use function Psl\Vec\values;

/**
 * @api
 * @implements IteratorAggregate<int, Route>
 */
final readonly class RouteList implements Countable, IteratorAggregate
{
    /** @var list<Route> */
    private array $items;

    public function __construct(Route ...$items)
    {
        $this->items = values($items);
    }

    #[Override]
    public function count(): int
    {
        return count($this->items);
    }

    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function match(ServerRequestInterface $request): ?RouteMatch
    {
        $method = Method::fromRequest($request);

        foreach ($this->items as $route) {
            if ($route->method !== $method) {
                continue;
            }

            $result = $route->pattern->extract($request->getUri());

            if ($result) {
                return new RouteMatch($route->handler, $result);
            }
        }

        return null;
    }
}
