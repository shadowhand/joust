# Route discovery and caching

Instead of constructing each `Route` manually, annotate PSR-15 handler classes with `#[AsRoute]` and let `RouteCollector` discover them. The attribute is repeatable, so one handler class can serve more than one method or pattern.

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

`RouteCollector::in()` scans the directory for instantiatable classes, skips classes that do not implement `RequestHandlerInterface`, and yields a route for each `AsRoute` attribute it finds. `RouteCollector::on()` discovers routes on a single handler class.

## Create and read a route cache

`RouteCache` uses a collector to scan a source directory and serializes the resulting `RouteList` to a file. On application startup, read the cached list when it exists; otherwise write it and then read it:

```php
use Joust\Cache\RouteCache;
use Joust\Cache\RouteCollector;
use Joust\RouteList;

$cache = new RouteCache(new RouteCollector());
$cacheFile = __DIR__ . '/var/cache/routes.phpcache';

if (!is_file($cacheFile)) {
    $cache->write(__DIR__ . '/src/Routes', $cacheFile);
}

/** @var RouteList $routes */
$routes = $cache->read($cacheFile);
```

Regenerate the cache when route attributes or handler classes change (for example, as part of deployment). The cache is PHP-serialized data; only read cache files created and controlled by your application. For how to pass the resulting `RouteList` to a router, see [PSR-15 integration](psr-15.md). For manually defined routes, see [Routes](routes.md).
