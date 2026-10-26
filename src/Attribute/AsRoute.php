<?php

declare(strict_types=1);

namespace Joust\Attribute;

use Attribute;
use Joust\Method;
use SensitiveParameter;

/**
 * Mark a request handler as a route.
 *
 * MAY be used in combination with {@see Middleware}.
 *
 * @api
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
final readonly class AsRoute
{
    public Method $method;

    // @mago-expect lint:excessive-parameter-list
    public function __construct(
        Method|string $method = Method::Get,
        public ?string $baseUrl = null,
        public ?string $scheme = null,
        public ?string $username = null,
        #[SensitiveParameter]
        public ?string $password = null,
        public ?string $host = null,
        public ?string $path = null,
        public ?string $query = null,
        public ?string $fragment = null,
    ) {
        $this->method = $method instanceof Method ? $method : Method::from($method);
    }
}
