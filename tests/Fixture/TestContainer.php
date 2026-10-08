<?php

declare(strict_types=1);

namespace Joust\Tests\Fixture;

use Override;
use Psr\Container\ContainerInterface;

use function Psl\Iter\contains_key;

final readonly class TestContainer implements ContainerInterface
{
    /**
     * @param array<string, mixed> $entries
     */
    public function __construct(
        private array $entries = [],
    ) {}

    #[Override]
    public function get($id): mixed
    {
        return $this->entries[$id] ?? throw new EntryNotFound($id);
    }

    #[Override]
    public function has($id): bool
    {
        return contains_key($this->entries, $id);
    }
}
