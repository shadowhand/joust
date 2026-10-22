# Applications and PSR-15 routing

Joust builds an HTTP application from [PSR-15][] middleware. An `App` runs its middleware in the order it was added,
then calls a fallback request handler. A `Router` is middleware that dispatches a `RouteList`, so it is added to the
application like any other middleware.

[PSR-15]: https://www.php-fig.org/psr/psr-15/

## Create an application

`App` takes a [PSR-11][] container and a fallback handler. `NotFoundHandler` responds with a JSON problem response
with status 404, which makes it a good fallback when no route matches.

[PSR-11]: https://www.php-fig.org/psr/psr-11/

```php
use Acme\Middleware\ParseJsonBody; // your own PSR-15 middleware
use Errata\Middleware\ProblemMiddleware;
use Joust\App;
use Joust\NotFoundHandler;
use Joust\Response\JsonResponseFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/** @var ContainerInterface $container */
/** @var ResponseFactoryInterface $responseFactory */
/** @var StreamFactoryInterface $streamFactory */
$json = new JsonResponseFactory($responseFactory, $streamFactory);

$app = new App($container, new NotFoundHandler($json));
$app->add(new ProblemMiddleware($responseFactory, $streamFactory));
$app->add(ParseJsonBody::class);
$app->collectRoutes(__DIR__ . '/src/Routes');

$response = $app->handle($request);
```

`$responseFactory` and `$streamFactory` are [PSR-17][] implementations.

[PSR-17]: https://www.php-fig.org/psr/psr-17/

`add()` accepts a middleware instance or the class name of a middleware. A class name is resolved from the container
when a request reaches it, so middleware that is never reached is never created. `add()`, `collectRoutes()`, and
`cacheRoutes()` return the application, so calls can be chained.

The first middleware added is the outermost: it sees the request first and the response last. `App` does not catch
exceptions; add an error middleware such as Errata's `ProblemMiddleware` first so that it wraps everything else.

`App` is itself a request handler. Call `handle()` with a PSR-7 server request to get a response.

## Add routes

`collectRoutes()` discovers routes from `#[AsRoute]` attributes on the handlers in a directory and adds a `Router` for
them. `cacheRoutes()` does the same using a route cache file, which it writes when the file does not exist; see
[Route discovery and caching](cache.md). The router is added at the point either method is called, like `add()`.

A `Router` can also be created from any `RouteList` and added directly:

```php
use Joust\RouteList;
use Joust\Router;

/** @var RouteList $routes */
$app->add(new Router($container, $routes));
```

The router resolves matched handlers from the container using the class name in the `RouteHandler` of the route.
Register each routed handler in that container. Before calling a matched route handler, Joust adds a `RouteMatch`
request attribute. A handler can retrieve the matched route and its captured path values with
`RouteMatch::fromRequest($request)`; see [Routes](routes.md).

If no route matches, the router passes the unchanged request to the next handler, allowing later middleware or the
fallback handler to respond.

## Without an application

`Router` is standard PSR-15 middleware and `Pipeline` runs a list of middleware before a request handler, so both can
be used in another middleware stack:

```php
use Joust\NotFoundHandler;
use Joust\Pipeline;

$response = new Pipeline([$router], new NotFoundHandler($json))->handle($request);
```
