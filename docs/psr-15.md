# PSR-15 routing

Joust provides a `Router` to dispatch a `RouteList`. The `Router` can be used as [PSR-15][] middleware or as a request
handler. It resolves matched handlers from a [PSR-11][] container using the class name in the `RouteHandler` of the
route. Register each routed handler in that container.

[PSR-11]: https://www.php-fig.org/psr/psr-11/
[PSR-15]: https://www.php-fig.org/psr/psr-15/

```php
use Joust\Response\JsonResponseFactory;
use Joust\RouteList;
use Joust\Router;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/** @var RouteList $routes */
/** @var ContainerInterface $container */
/** @var ResponseFactoryInterface $responseFactory */
/** @var StreamFactoryInterface $streamFactory */
$json = new JsonResponseFactory($responseFactory, $streamFactory);

$router = new Router($container, $json, $routes);
```

`$responseFactory` and `$streamFactory` are [PSR-17][] implementations. `Router` uses `JsonResponseFactory` to format
the unmatched-route problem response when it handles the request itself. To discover routes from handler attributes
and optionally cache them, see [Route discovery and caching](cache.md).

[PSR-17]: https://www.php-fig.org/psr/psr-17/

Before calling a matched route handler, Joust adds a `RouteMatch` request attribute. A handler can retrieve the matched
route and its captured path values with `RouteMatch::fromRequest($request)`; see [Routes](routes.md).

## As middleware

Call `process()` when Joust is part of a larger middleware stack. A matching route is dispatched immediately. If no
route matches, the router passes the unchanged request to the next handler, allowing later middleware or a fallback
handler to respond.

```php
use Psr\Http\Server\RequestHandlerInterface;

/** @var RequestHandlerInterface $fallback */

$response = $router->process($request, $fallback);
```

## As a terminal handler

Call `handle()` when Joust should own the final route-not-found response. It dispatches a matching handler, or returns
a JSON problem response with status 404 when no route matches.

```php
$response = $router->handle($request);
```
