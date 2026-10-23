<div style="text-align:center;margin:0 auto;">
    <img src="docs/joust-banner.jpg" style="width:100%;max-width:1200px" alt="Joust Banner"/>
</div>

# Joust

♞♘ Just another JSON API kit.

## Installation

```sh
composer require joust/joust
```

Joust requires PSR interfaces only. Install a PSR-7 and PSR-17 implementation too, for example:

```sh
composer require nyholm/psr7
```

## Usage

Early stage of development, everything is in flux!

Create an application, add middleware and routes, and run it from your front controller with a PSR-7 request from your
SAPI integration:

```php
use Errata\Middleware\ProblemMiddleware;
use Joust\App;
use Joust\Handler\NotFound;
use Joust\Middleware\ParseJsonBody;
use Joust\Response\JsonResponseFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/** @var ContainerInterface $container */
/** @var ServerRequestInterface $request */
$factory = new Psr17Factory();
$json = new JsonResponseFactory($factory, $factory);

$app = new App($container, new NotFound($json));
$app->add(new ProblemMiddleware($factory, $factory));
$app->lazy(ParseJsonBody::class);
$app->collectRoutes(__DIR__ . '/../src/Routes');
$app->run($request);
```

The container provides each routed handler and every middleware added with `lazy()`. Route handlers are PSR-15 request
handlers with `#[AsRoute]` attributes. See the documentation below for details.

## Documentation

- [Routes](docs/routes.md): define routes, route lists, and access matched path parameters.
- [Route discovery and caching](docs/cache.md): register handler routes with `AsRoute` and cache discovered routes.
- [Applications and PSR-15 routing](docs/psr-15.md): build and run an application from middleware and routes, add
  middleware to individual route handlers, and parse JSON request bodies.
- [JSON responses](docs/responses.md): create JSON and problem responses and configure defaults.

## Demo

Run the todo API demo with PHP's built-in server:

```sh
composer install
composer demo
```

Open <http://localhost:8081> to try the API from the clickable home page. Todo data persists between requests in a JSON
file in the system temporary directory.

The API includes `GET /todos`, `GET /todos/:id`, `POST /todos`, and `PATCH /todos/:id/complete`. The home page also
demonstrates validation, not-found responses, malformed JSON, unrouted requests, and Errata's exception handling.

## Development

This project uses [Mago](https://mago.carthage.software/) for lint, formatting, and static analysis.

```sh
composer run fix       # automatically fix lint, analysis, and formatting issues
composer run format    # format source code
composer run check     # check style
composer run lint      # lint code
composer run analyze   # static analysis
composer run test      # unit testing with 100% coverage enforced
composer run verify    # run all verifications (check + lint + analyze + test)
```
