# Routes

Joust represents routes as values. A `Route` combines an HTTP `Method`, a [League URI `UrlPattern`][url-pattern],
and a `RouteHandler` naming the PSR-15 handler to invoke. A `RouteList` holds routes in matching order and finds
the first route whose method and URI pattern match a request.

[url-pattern]: https://uri.thephpleague.com/uri/7.0/url-pattern/

## Defining routes directly

```php
use Joust\Method;
use Joust\Route;
use Joust\RouteHandler;
use Joust\RouteList;
use League\Uri\UrlPattern;

$routes = new RouteList(
    new Route(
        Method::Get,
        UrlPattern::from('/todos/:id'),
        new RouteHandler(GetTodo::class),
    ),
);
```

The route pattern uses League URI's URL pattern syntax. Here `:id` captures a path variable. `RouteHandler` accepts
the class name of a PSR-15 request handler; the router resolves that class through a PSR-11 container when the
route matches. See [PSR-15 integration](psr-15.md) for the container and request flow.

`RouteList` is iterable and countable. Its `match()` method accepts a `ServerRequestInterface` and returns a `RouteMatch`
or `null`:

```php
$match = $routes->match($request);

if (null !== $match) {
    $handlerClass = $match->handler->name;
    $todoId = $match->result->path->integer('id');
}
```

`RouteMatch` carries the selected `RouteHandler` and League URI extraction `Result`. Access named path captures through
`result->path`, using methods such as `string('id')` or `integer('id')`. Joust attaches the match to the request under
the `RouteMatch::class` attribute before invoking a matched handler; inside that handler, retrieve it with
`RouteMatch::fromRequest($request)`.

## HTTP methods

`Method` is an enum for the standard methods `GET`, `HEAD`, `POST`, `PUT`, `PATCH`, `DELETE`, `CONNECT`, `OPTIONS`,
`TRACE`, and `QUERY`. Use its cases when constructing routes, for example `Method::Get` or `Method::Patch`.
`Method::fromRequest($request)` converts a request method to its enum case.

For route discovery from handler attributes, see [Route discovery and caching](cache.md).
