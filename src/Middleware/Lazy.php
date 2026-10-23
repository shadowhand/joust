<?php

declare(strict_types=1);

namespace Joust\Middleware;

use Override;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Psl\Type\class_string;

/**
 * Defers a middleware from being resolved until called.
 *
 * @api
 */
final readonly class Lazy implements MiddlewareInterface
{
    /**
     * @assert class-string<MiddlewareInterface> $name
     */
    public function __construct(
        private ContainerInterface $container,
        /** @var class-string<MiddlewareInterface> */
        private string $name,
    ) {
        class_string(MiddlewareInterface::class)->assert($this->name);
    }

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->resolve()->process($request, $handler);
    }

    private function resolve(): MiddlewareInterface
    {
        // @mago-expect analysis:mixed-return-statement
        return $this->container->get($this->name);
    }
}
