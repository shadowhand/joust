# Route discovery and caching

Instead of constructing each `Route` manually, annotate [PSR-15][] handler classes with `#[AsRoute]` and let a route
locator discover them. The attribute is repeatable, so one handler class can serve more than one method or pattern.

[PSR-15]: https://www.php-fig.org/psr/psr-15/

```php
use Joust\Attribute\AsRoute;
use Joust\Method;
use Joust\Response\JsonResponseFactory;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[AsRoute(Method::Get, '/todos')]
#[AsRoute(Method::Post, '/todos')]
final readonly class Todos implements RequestHandlerInterface
{
    public function __construct(private JsonResponseFactory $json) {}

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->json->respond(data: []);
    }
}
```

The attribute takes a method and a path. Pass a base URL when the route should match a specific origin. Without one,
the path matches any host:

```php
#[AsRoute(Method::Get, '/todos/:id', 'https://api.example.com')]
```

## Discovery

`RouteLocator` scans a directory for instantiatable classes, skips classes that do not implement
`RequestHandlerInterface`, and reads each handler's attributes:

```php
use Joust\Locator\RouteLocator;

$routes = new RouteLocator(__DIR__ . '/../src');
```

`RouteReader` does the same for a single handler class:

```php
use Joust\Locator\RouteReader;

$routes = new RouteReader(Todos::class);
```

Both implement `RouteList`, so they can be passed directly to a `RouteMatcher` or a
[`Router`](psr-15.md#add-routes). The readers also pick up `#[Middleware]` attributes on the handler class; see
[Route middleware](psr-15.md#route-middleware).

## Create and read a route cache

`RouteCache` materializes any `RouteList` into a `RouteCollection` and serializes it to a file; `read()` unserializes
the file back into that collection:

```php
use Joust\Locator\RouteCache;
use Joust\Locator\RouteLocator;

$cacheFile = __DIR__ . '/../var/cache/routes.phpcache';

$cache = new RouteCache();
$cache->write(new RouteLocator(__DIR__ . '/../src'), $cacheFile);

// On a later request, or in production:
$routes = $cache->read($cacheFile);
```

An `App` can generate the cache in one step. Passing a cache file to `collectRoutes()` discovers the routes, writes
them to the file, and adds a `Router` for the discovered routes. Call it from a build or deployment script to
regenerate the cache:

```php
$app->collectRoutes(__DIR__ . '/../src', __DIR__ . '/../var/cache/routes.phpcache');
```

At runtime, `cachedRoutes()` adds a `Router` for the routes in an existing cache file:

```php
$app->cachedRoutes(__DIR__ . '/../var/cache/routes.phpcache');
```

Use `collectRoutes(__DIR__ . '/../src')` without a cache file to discover routes on every request.

Regenerate the cache when route attributes or handler classes change (for example, as part of deployment). The cache
is PHP-serialized data; only read cache files created and controlled by your application. For how to pass the resulting
`RouteList` to a router, see [Applications and PSR-15 routing](psr-15.md). For manually defined routes, see
[Routes](routes.md).
