# Route discovery and caching

Instead of constructing each `Route` manually, annotate [PSR-15][] handler classes with `#[AsRoute]` and let a
`RouteFinder` discover them. The attribute is repeatable, so one handler class can serve more than one method or
pattern.

[PSR-15]: https://www.php-fig.org/psr/psr-15/

```php
use Joust\Attribute\AsRoute;
use Joust\Method;
use Joust\Response\JsonResponseFactory;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[AsRoute(Method::Get, path: '/todos')]
#[AsRoute(Method::Post, path: '/todos')]
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

The first argument is the HTTP method. The remaining arguments are [League URI URL pattern][url-pattern] components:
`baseUrl`, `scheme`, `username`, `password`, `host`, `path`, `query`, and `fragment`. Pass components by name to keep
the attribute readable:

```php
#[AsRoute(Method::Get, path: '/todos/:id', baseUrl: 'https://api.example.com')]
```

Components left `null` are not constrained. Without a `baseUrl` or `host`, the path matches any host.

[url-pattern]: https://uri.thephpleague.com/uri/7.0/url-pattern/

## Discovery

`RouteFinder` scans a directory for PHP classes using Composer's class map generator, skips classes that do not
implement `RequestHandlerInterface`, and reads the attributes of each handler:

```php
use Joust\Finder\RouteFinder;
use Joust\Finder\RouteFinderSettings;

$routes = new RouteFinder(new RouteFinderSettings(routeDir: __DIR__ . '/../src'));
```

`RouteFinderSettings` defaults `routeDir` to the `src` directory of the Composer root package. It also accepts an
application `baseUrl`, applied to every route that does not declare its own:

```php
$settings = new RouteFinderSettings(baseUrl: 'https://api.example.com');
```

`RouteReader` does the same for a single handler class. It accepts the handler class name and an optional base URL:

```php
use Joust\Finder\RouteReader;

$routes = new RouteReader(Todos::class);
```

Both implement `RouteList`, so they can be passed directly to a `RouteMatcher` or a
[`RouterMiddleware`](psr-15.md#add-routes). The readers also pick up `#[Middleware]` attributes on the handler class;
see [Route middleware](psr-15.md#route-middleware).

## Create and read a route cache

`RouteCache` materializes any `RouteList` into a `RouteCollection` and serializes it to a file; `read()` unserializes
the file back into that collection:

```php
use Joust\Cache\RouteCache;
use Joust\Cache\RouteCacheSettings;
use Joust\Finder\RouteFinder;
use Joust\Finder\RouteFinderSettings;

$cache = new RouteCache(new RouteCacheSettings(__DIR__ . '/../var/joust-routes.php.cache'));

$cache->write(new RouteFinder(new RouteFinderSettings(__DIR__ . '/../src')));

// On a later request, or in production:
$routes = $cache->read();
```

`RouteCacheSettings` defaults `cacheFile` to `var/joust-routes.php.cache` under the Composer root package.

An `App` reads a cache through the `RouteCache` registered in its container. `cachedRouter()` adds a router for the
routes in the configured cache file, and `liveRouter()` adds a router for the `RouteFinder` in the container:

```php
use Joust\App;
use Joust\Cache\RouteCache;
use Joust\Cache\RouteCacheSettings;
use Joust\Finder\RouteFinder;
use Joust\Finder\RouteFinderSettings;
use Psr\Container\ContainerInterface;

$routeFinder = new RouteFinder(new RouteFinderSettings(__DIR__ . '/../src'));
$routeCache = new RouteCache(new RouteCacheSettings(__DIR__ . '/../var/joust-routes.php.cache'));

// Register them in your container as RouteFinder::class and RouteCache::class.
/** @var ContainerInterface $container */
$app = new App($container);

$app->liveRouter(); // read RouteFinder::class and route using its result
$app->cachedRouter(); // read RouteCache::class and route using the cached routes
```

Use `liveRouter()` during development. To generate the cache, run the `write()` code in a build or deployment script,
then call `cachedRouter()` at runtime.

Regenerate the cache when route attributes or handler classes change (for example, as part of deployment). The cache
is PHP-serialized data; only read cache files created and controlled by your application. For how to pass the resulting
`RouteList` to a router, see [Applications and PSR-15 routing](psr-15.md). For manually defined routes, see
[Routes](routes.md).
