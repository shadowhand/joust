# Applications and PSR-15 routing

Joust builds an HTTP application from [PSR-15][] middleware. An `App` runs its middleware in the order it was added,
then calls a fallback request handler. A `Router` is middleware that dispatches a `RouteList`, so it is added to the
application like any other middleware.

[PSR-15]: https://www.php-fig.org/psr/psr-15/

## Create an application

`App` takes a [PSR-11][] container and an optional fallback handler. Middleware and routes are added to it, and
`run()` handles a request and sends the response:

[PSR-11]: https://www.php-fig.org/psr/psr-11/

```php
// public/index.php
use Joust\Middleware\ParseJsonBody;
use Errata\Middleware\ProblemMiddleware;
use Joust\App;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/** @var ContainerInterface $container */
$app = new App($container);
$app->lazy(ProblemMiddleware::class);
$app->lazy(ParseJsonBody::class);
$app->collectRoutes(__DIR__ . '/../src/Routes');

/** @var ServerRequestInterface $request */
$app->run($request);
```

The container must provide each routed handler and every middleware added with `lazy()`. It does not need to provide
middleware added as instances, or the fallback handler when one is passed to the constructor.

`add()` accepts a middleware instance. `lazy()` accepts the class name of a middleware and resolves it from the
container when a request reaches it, so middleware that is never reached is never created. `add()`, `lazy()`,
`collectRoutes()`, and `cachedRoutes()` return the application, so calls can be chained.

The first middleware added is the outermost: it sees the request first and the response last. `App` does not catch
exceptions; add an error middleware such as Errata's `ProblemMiddleware` first so that it wraps everything else.

## Fallback handler

When no middleware responds, for example because no route matches, `App` calls its fallback handler. The default is
`Joust\Handler\NotFound`, which responds with a JSON problem response with status 404. When no fallback is passed,
`App` resolves it from the container under that class name.

To avoid the container dependency, pass any request handler as the second argument:

```php
$app = new App($container, new My404());
```

## Run and handle requests

`run()` accepts a [PSR-7][] server request, handles it, and sends the response with [http-interop/response-sender][].
Joust does not create requests from globals; build the request in your front controller with the SAPI integration you
already use, such as your framework's PSR-7 bridge or [nyholm/psr7-server][]:

[PSR-7]: https://www.php-fig.org/psr/psr-7/
[http-interop/response-sender]: https://github.com/http-interop/response-sender
[nyholm/psr7-server]: https://github.com/nyholm/psr7-server

```php
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;

$factory = new Psr17Factory();
$creator = new ServerRequestCreator($factory, $factory, $factory, $factory);

$app->run($creator->fromGlobals());
```

Before sending, `run()` throws a `Psl\Exception\InvariantViolationException` when headers were already sent or when
output is waiting in an output buffer. An empty output buffer, as the PHP built-in server keeps around router scripts,
is allowed. Call `handle()` and emit the response yourself when you need to log, wrap, or otherwise control emission:

```php
use Nyholm\Psr7\ServerRequest;

$response = $app->handle(new ServerRequest('GET', '/todos'));
```

`App` is itself a [PSR-15][] request handler, so `handle()` works without touching globals or sending output, for
example in tests.

## Add routes

`collectRoutes()` discovers routes from `#[AsRoute]` attributes on the handlers in a directory and adds a `Router`
for them. Passing a cache file as the second argument also writes the routes to that file, to generate a cache.
`cachedRoutes()` adds a `Router` for the routes in an existing route cache file; see
[Route discovery and caching](cache.md). The router is added at the point either method is called, like `add()`.

A `Router` can also be created from any `RouteList` and added directly:

```php
use Joust\Middleware\Router;
use Joust\RouteList;

/** @var RouteList $routes */
$app->add(new Router($container, $routes));
```

The router resolves matched handlers from the container using the class name in the `RouteHandler` of the route.
Register each routed handler in that container. Before calling a matched route handler, Joust adds a `RouteMatch`
request attribute. A handler can retrieve the matched route and its captured path values with
`RouteMatch::fromRequest($request)`; see [Routes](routes.md).

If no route matches, the router passes the unchanged request to the next handler, allowing later middleware or the
fallback handler to respond.

## Route middleware

Middleware can also be added to the routes of a single handler. Declare it on the handler class with the repeatable
`#[Middleware]` attribute; the route reader picks it up along with `#[AsRoute]`, and route caches keep it:

```php
use Acme\Middleware\RequireAuth;
use Joust\Attribute\AsRoute;
use Joust\Attribute\Middleware;
use Joust\Method;
use Joust\Middleware\ParseJsonBody;
use Psr\Http\Server\RequestHandlerInterface;

#[AsRoute(Method::Post, '/todos')]
#[Middleware(RequireAuth::class)]
#[Middleware(ParseJsonBody::class)]
final readonly class CreateTodo implements RequestHandlerInterface
{
    // ...
}
```

For routes defined directly, pass middleware class names to `RouteHandler`, or add them with `withMiddleware()`, which
returns a copy:

```php
use Joust\RouteHandler;

$handler = new RouteHandler(CreateTodo::class, [RequireAuth::class]);
$handler = $handler->withMiddleware(ParseJsonBody::class);
```

When a route matches, the router runs its middleware in the order it was declared, then the handler. Route middleware
runs after the application middleware added before the router, and after the `RouteMatch` attribute is added, so it
can read the matched route. Each middleware is resolved from the container when a request reaches it; register it
there along with the routed handlers.

## Parse JSON request bodies

`ParseJsonBody` decodes a JSON request body onto the request, so handlers read it with `$request->getParsedBody()`. It
treats `application/json` and vendor-specific `application/*+json` media types as JSON, ignoring parameters such as
`charset`. Add it to the application, or to the routes that accept JSON:

```php
use Joust\Attribute\AsRoute;
use Joust\Attribute\Middleware;
use Joust\Method;
use Joust\Middleware\ParseJsonBody;
use Psr\Http\Server\RequestHandlerInterface;

#[AsRoute(Method::Post, '/todos')]
#[Middleware(ParseJsonBody::class)]
final readonly class CreateTodo implements RequestHandlerInterface
{
    // ...
}
```

It uses `JsonResponseFactory` to answer invalid payloads, so register it in the container along with the routed
handlers. Requests that are not JSON or have an empty body pass through unchanged. A body that cannot be decoded into
a JSON object or array receives a `400 Bad Request` problem response instead of reaching the next handler.

## Without an application

`Router` is standard PSR-15 middleware and `Pipeline` runs a list of middleware before a request handler, so both can
be used in another middleware stack:

```php
use Joust\Handler\NotFound;
use Joust\Handler\Pipeline;

$response = new Pipeline([$router], new NotFound($json))->handle($request);
```
