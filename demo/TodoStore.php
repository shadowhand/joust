<?php

declare(strict_types=1);

namespace Joust\Demo;

use Psl\Type;
use Psl\Type\Exception\CoercionException;

use function file_get_contents;
use function file_put_contents;
use function is_file;
use function json_decode;
use function json_encode;
use function sys_get_temp_dir;

final class TodoStore
{
    private const array DEFAULT_SEED = [
        ['id' => 1, 'title' => 'Write tests', 'completed' => false],
        ['id' => 2, 'title' => 'Ship the joust demo', 'completed' => true],
        ['id' => 3, 'title' => 'Water the plants', 'completed' => false],
    ];

    /** @var list<array{id: int, title: string, completed: bool}> */
    private array $todos;

    public function __construct(
        private readonly string $path = '',
    ) {
        $this->reload();
    }

    /**
     * @return list<array{id: int, title: string, completed: bool}>
     */
    public function all(): array
    {
        $this->reload();

        return $this->todos;
    }

    /**
     * @return null|array{id: int, title: string, completed: bool}
     */
    public function find(int $id): ?array
    {
        $this->reload();

        foreach ($this->todos as $todo) {
            if ($todo['id'] === $id) {
                return $todo;
            }
        }

        return null;
    }

    /**
     * @return array{id: int, title: string, completed: bool}
     */
    public function create(string $title): array
    {
        $this->reload();

        $todo = ['id' => $this->nextId(), 'title' => $title, 'completed' => false];
        $this->todos[] = $todo;
        $this->persist();

        return $todo;
    }

    /**
     * @return null|array{id: int, title: string, completed: bool}
     */
    public function complete(int $id): ?array
    {
        $this->reload();

        foreach ($this->todos as $index => $todo) {
            if ($todo['id'] !== $id) {
                continue;
            }

            $completed = [...$todo, 'completed' => true];
            $this->todos[$index] = $completed;
            $this->persist();

            return $completed;
        }

        return null;
    }

    /**
     * @return Type\TypeInterface<list<array{id: int, title: string, completed: bool}>>
     */
    private static function todosType(): Type\TypeInterface
    {
        return Type\vec(Type\shape([
            'id' => Type\int(),
            'title' => Type\string(),
            'completed' => Type\bool(),
        ]));
    }

    private function reload(): void
    {
        $file = $this->file();
        $contents = is_file($file) ? file_get_contents($file) : '';

        if (false === $contents || '' === $contents) {
            $this->todos = self::DEFAULT_SEED;

            return;
        }

        try {
            $this->todos = self::todosType()->coerce(json_decode($contents, associative: true));
        } catch (CoercionException) {
            $this->todos = self::DEFAULT_SEED;
        }
    }

    private function persist(): void
    {
        file_put_contents($this->file(), json_encode($this->todos));
    }

    private function nextId(): int
    {
        $next = 1;

        foreach ($this->todos as $todo) {
            $next = $todo['id'] >= $next ? $todo['id'] + 1 : $next;
        }

        return $next;
    }

    private function file(): string
    {
        return '' === $this->path ? sys_get_temp_dir() . '/joust-demo-todos.json' : $this->path;
    }
}
