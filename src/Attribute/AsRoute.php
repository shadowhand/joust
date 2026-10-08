<?php

declare(strict_types=1);

namespace Joust\Attribute;

use Attribute;
use Joust\Method;
use League\Uri\UrlPattern;

use function is_string;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class AsRoute
{
    public Method $method;
    public UrlPattern $pattern;

    /**
     * @param Method|non-empty-string $method
     * @param UrlPattern|string $pattern
     */
    public function __construct(Method|string $method = Method::Get, UrlPattern|string $pattern = '/')
    {
        $this->method = is_string($method) ? Method::from($method) : $method;
        $this->pattern = is_string($pattern) ? UrlPattern::from($pattern) : $pattern;
    }
}
