<?php

declare(strict_types=1);

namespace Joust;

use Override;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function Psl\Type\class_string;
use function Psl\Type\instance_of;

/**
 * Resolves middleware from a container when a request reaches it.
 *
 * @internal
 */
final readonly class LazyMiddleware implements MiddlewareInterface
{
    /**
     * @phpstan-assert class-string<MiddlewareInterface> $name
     */
    public function __construct(
        private ContainerInterface $container,
        private string $name,
    ) {
        class_string(MiddlewareInterface::class)->assert($this->name);
    }

    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $middleware = instance_of(MiddlewareInterface::class)->assert($this->container->get($this->name));

        return $middleware->process($request, $handler);
    }
}
