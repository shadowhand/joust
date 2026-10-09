# PSR-15 routing

Joust offers two ways to dispatch a `RouteList` to request handlers: `RoutingMiddleware` and `RoutingHandler`. Both resolve matched handlers from a PSR-11 container using the class name stored in each route's `RouteHandler`. Register each routed handler in that container.

## As middleware

Use `RoutingMiddleware` when Joust is part of a larger PSR-15 middleware stack. A matching route is dispatched immediately. If no route matches, the middleware passes the unchanged request to the next handler, allowing later middleware or a fallback handler to respond.

```php
use Joust\Middleware\RoutingMiddleware;
use Joust\RouteList;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\RequestHandlerInterface;

/** @var RouteList $routes */
/** @var ContainerInterface $container */
/** @var RequestHandlerInterface $fallback */

$routing = new RoutingMiddleware($container, $routes);
$response = $routing->process($request, $fallback);
```

Before calling a matched route handler, Joust adds a `RouteMatch` request attribute. A handler can retrieve the matched route and its captured path values with `RouteMatch::fromRequest($request)`; see [Routes](routes.md).

## As a terminal handler

Use `RoutingHandler` when Joust should own the final route-not-found response. It dispatches a matching handler, or returns a JSON problem response with status 404 when no route matches.

```php
use Joust\Handler\RoutingHandler;
use Joust\Response\JsonResponseFactory;
use Joust\RouteList;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/** @var RouteList $routes */
/** @var ContainerInterface $container */
/** @var ResponseFactoryInterface $responseFactory */
/** @var StreamFactoryInterface $streamFactory */
$json = new JsonResponseFactory($responseFactory, $streamFactory);

$router = new RoutingHandler($container, $json, $routes);
$response = $router->handle($request);
```

`$responseFactory` and `$streamFactory` are PSR-17 implementations. The `RoutingHandler` needs `JsonResponseFactory` to format its unmatched-route problem response. To discover routes from handler attributes and optionally cache them, see [Route discovery and caching](cache.md).
