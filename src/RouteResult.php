<?php

declare(strict_types=1);

namespace Joust;

use League\Uri\UrlPattern\Result;
use Psr\Http\Message\ServerRequestInterface;

use function Psl\Type\instance_of;

/**
 * @api
 */
final readonly class RouteResult
{
    public static function fromRequest(ServerRequestInterface $request): self
    {
        return instance_of(self::class)->assert($request->getAttribute(self::class));
    }

    public function __construct(
        public Route $route,
        public Result $result,
    ) {}

    public function inject(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request->withAttribute(self::class, $this);
    }
}
