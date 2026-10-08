<?php

declare(strict_types=1);

namespace Joust\Demo;

use Override;
use Psr\Container\ContainerInterface;
use RuntimeException;

use function Psl\Iter\contains_key;

final readonly class Container implements ContainerInterface
{
    /**
     * @param array<class-string, object> $entries
     */
    public function __construct(
        private array $entries,
    ) {}

    #[Override]
    public function get(string $id): object
    {
        return $this->entries[$id] ?? throw new RuntimeException("Unknown service '{$id}'.");
    }

    #[Override]
    public function has(string $id): bool
    {
        return contains_key($this->entries, $id);
    }
}
