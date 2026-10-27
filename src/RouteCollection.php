<?php

declare(strict_types=1);

namespace Joust;

use ArrayIterator;
use JsonSerializable;
use Override;
use Psl\Type\TypeInterface;
use Traversable;

use function Psl\Type\converted;
use function Psl\Type\instance_of;
use function Psl\Type\vec;
use function Psl\Vec\values;

/**
 * A collection of routes.
 *
 * @api
 */
final readonly class RouteCollection implements RouteList, JsonSerializable
{
    /**
     * @return TypeInterface<self>
     */
    public static function type(): TypeInterface
    {
        return converted(
            vec(Route::type()),
            instance_of(self::class),
            // @mago-expect analysis:imprecise-type
            static fn(array $routes): self => new self(...$routes),
        );
    }

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

    /**
     * @return list<Route>
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return $this->list;
    }
}
