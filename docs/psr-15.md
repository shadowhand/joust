# Applications and PSR-15 routing

Joust builds an HTTP application from [PSR-15][] middleware. An `App` runs its middleware in the order it was added,
then calls a fallback request handler. A `Router` is middleware that dispatches a `RouteList`, so it is added to the
application like any other middleware.

[PSR-15]: https://www.php-fig.org/psr/psr-15/

## Create an application

`App` takes a [PSR-11][] container and a [PSR-17][] factory. Middleware and routes are added to it, and `run()` handles
the current request and sends the response:

[PSR-11]: https://www.php-fig.org/psr/psr-11/
[PSR-17]: https://www.php-fig.org/psr/psr-17/

```php
// public/index.php
use Acme\Middleware\ParseJsonBody; // your own PSR-15 middleware
use Errata\Middleware\ProblemMiddleware;
use Joust\App;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;

/** @var ContainerInterface $container */
$factory = new Psr17Factory();

$app = new App($container, $factory);
$app->add(new ProblemMiddleware($factory, $factory));
$app->add(ParseJsonBody::class);
$app->collectRoutes(__DIR__ . '/../src/Routes');
$app->run();
```

The factory must create responses, streams, server requests, URIs, and uploaded files. Joust does not depend on any
PSR-7 implementation; any package with a factory that implements all five PSR-17 interfaces works, such as
`Nyholm\Psr7\Factory\Psr17Factory` from `nyholm/psr7` or `GuzzleHttp\Psr7\HttpFactory` from `guzzlehttp/psr7`.
Implementations with a separate class per factory, such as Laminas Diactoros, need a small class that implements the
five interfaces and delegates to each factory.

`add()` accepts a middleware instance or the class name of a middleware. A class name is resolved from the container
when a request reaches it, so middleware that is never reached is never created. `add()`, `collectRoutes()`, and
`cacheRoutes()` return the application, so calls can be chained.

The first middleware added is the outermost: it sees the request first and the response last. `App` does not catch
exceptions; add an error middleware such as Errata's `ProblemMiddleware` first so that it wraps everything else.

## Fallback handler

When no middleware responds, for example because no route matches, `App` calls its fallback handler. By default this
is a `NotFoundHandler`, built with the application's factory, which responds with a JSON problem response with status
404. Pass another request handler as the third argument to replace it:

```php
use Joust\App;
use Joust\NotFoundHandler;
use Joust\Response\JsonResponseFactory;
use Joust\Response\JsonResponseSettings;
use Nyholm\Psr7\Factory\Psr17Factory;

$factory = new Psr17Factory();
$json = new JsonResponseFactory($factory, $factory, new JsonResponseSettings(pretty: true));

$app = new App($container, $factory, new NotFoundHandler($json));
```

## Run and handle requests

`run()` creates a PSR-7 server request from the PHP globals with the `ServerRequestCreator` from `nyholm/psr7-server`,
using the application's factory. It passes the request through the application and sends the response with
`Joust\Response\SapiEmitter`, which uses [http-interop/response-sender][response-sender]. Before sending,
`SapiEmitter` throws a `RuntimeException` if headers were already sent, naming the file and line where output started,
or if any output is waiting in an output buffer.

[response-sender]: https://github.com/http-interop/response-sender

Pass your own `ServerRequestCreatorInterface` or `Joust\Response\Emitter` to the constructor to change either step:

```php
use Joust\Response\Emitter;
use Joust\Response\SapiEmitter;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

final class LoggingEmitter implements Emitter
{
    public function __construct(
        private readonly Emitter $emitter,
        private readonly LoggerInterface $logger,
    ) {}

    public function emit(ResponseInterface $response): void
    {
        $this->logger->info('Sending response', ['status' => $response->getStatusCode()]);
        $this->emitter->emit($response);
    }
}

$app = new App($container, $factory, emitter: new LoggingEmitter(new SapiEmitter(), $logger));
```

`App` is itself a [PSR-15][] request handler. Call `handle()` with a server request to get a response without touching
globals or sending output, for example in tests:

```php
use Nyholm\Psr7\ServerRequest;

$response = $app->handle(new ServerRequest('GET', '/todos'));
```

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

## Route middleware

Middleware can also be added to the routes of a single handler. Declare it on the handler class with the repeatable
`#[Middleware]` attribute; the route collector reads it along with `#[AsRoute]`, and route caches keep it:

```php
use Acme\Middleware\ParseJsonBody; // your own PSR-15 middleware
use Acme\Middleware\RequireAuth;
use Joust\Attribute\AsRoute;
use Joust\Attribute\Middleware;
use Joust\Method;
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

$handler = new RouteHandler(CreateTodo::class, RequireAuth::class);
$handler = $handler->withMiddleware(ParseJsonBody::class);
```

When a route matches, the router runs its middleware in the order it was declared, then the handler. Route middleware
runs after the application middleware added before the router, and after the `RouteMatch` attribute is added, so it
can read the matched route. Each middleware is resolved from the container when a request reaches it; register it
there along with the routed handlers.

## Without an application

`Router` is standard PSR-15 middleware and `Pipeline` runs a list of middleware before a request handler, so both can
be used in another middleware stack:

```php
use Joust\NotFoundHandler;
use Joust\Pipeline;

$response = new Pipeline([$router], new NotFoundHandler($json))->handle($request);
```
