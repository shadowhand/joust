<?php

declare(strict_types=1);

namespace Joust;

use League\Uri\UrlPattern\Result;
use Psr\Http\Message\ServerRequestInterface;

use function Psl\Type\instance_of;

/**
 * @api
 */
final readonly class RouteMatch
{
    public static function fromRequest(ServerRequestInterface $request): self
    {
        return instance_of(self::class)->assert($request->getAttribute(self::class));
    }

    public function __construct(
        public RouteHandler $handler,
        public Result $result,
    ) {}
}
