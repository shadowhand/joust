<?php

declare(strict_types=1);

namespace Joust;

use ArrayIterator;
use Override;
use Traversable;

use function Psl\Vec\values;

/**
 * A collection of routes.
 *
 * @api
 */
final readonly class RouteCollection implements RouteList
{
    /** @var list<Route> */
    private array $list;

    public function __construct(Route ...$list)
    {
        $this->list = values($list);
    }

    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->list);
    }
}
