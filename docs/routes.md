# Routes

Joust represents routes as values. A `Route` combines an HTTP `Method`, a [League URI `UrlPattern`][url-pattern],
and a `RouteHandler` naming the PSR-15 handler to invoke. A `RouteList` is an iterable collection of routes in matching
order, and a `RouteMatcher` finds the first route whose method and URI pattern match a request.

[url-pattern]: https://uri.thephpleague.com/uri/7.0/url-pattern/

## Defining routes directly

`RouteCollection` is the standard `RouteList` implementation for routes defined in code:

```php
use Joust\Method;
use Joust\Route;
use Joust\RouteCollection;
use Joust\RouteHandler;
use League\Uri\UrlPatternBuilder;

$pattern = new UrlPatternBuilder()
    ->path('/todos/:id')
    ->build();

$routes = new RouteCollection(
    new Route(Method::Get, $pattern, new RouteHandler(GetTodo::class)),
);
```

The route pattern uses League URI's URL pattern syntax. `UrlPatternBuilder` builds a path pattern; here `:id` captures
a path variable. Pass a base URL to match a specific origin. Without one, the path matches any host:

```php
$pattern = new UrlPatternBuilder(baseUrl: 'https://api.example.com')
    ->path('/todos/:id')
    ->build();
```

`RouteHandler` accepts the class name of a PSR-15 request handler; the router resolves that class through a PSR-11
container when the route matches. See [Applications and PSR-15 routing](psr-15.md) for the container and request
flow. A `RouteHandler` also holds a list of middleware class names to run before the handler; see
[Route middleware](psr-15.md#route-middleware).

`RouteList` is an interface extending `IteratorAggregate`, so any iterable source of routes can implement it.
`RouteCollection` and the route locators in [Route discovery and caching](cache.md) are the built-in implementations.

## Matching requests

`RouteMatcher` matches a request against a route list:

```php
use Joust\RouteMatcher;

$match = new RouteMatcher($routes)->match($request);

if (null !== $match) {
    $handlerClass = $match->handler->name;
    $todoId = $match->result->path->integer('id');
}
```

`match()` returns a `RouteMatch` or `null`. `RouteMatch` carries the selected `RouteHandler` and League URI extraction
`Result`. Access named path captures through `result->path`, using methods such as `string('id')` or `integer('id')`.

Joust attaches the match to the request under the `RouteMatch::class` attribute before invoking a matched handler;
inside that handler, retrieve it with `RouteMatch::fromRequest($request)`.

## HTTP methods

`Method` is an enum for the standard methods `GET`, `HEAD`, `POST`, `PUT`, `PATCH`, `DELETE`, `CONNECT`, `OPTIONS`,
`TRACE`, and `QUERY`. Use its cases when constructing routes, for example `Method::Get` or `Method::Patch`.
`Method::fromRequest($request)` converts a request method to its enum case.

For route discovery from handler attributes, see [Route discovery and caching](cache.md).
