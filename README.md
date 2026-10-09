<div style="text-align:center;margin:0 auto;">
    <img src="docs/joust-banner.jpg" style="width:100%;max-width:1200px" alt="Joust Banner"/>
</div>

# Joust

♞♘ Just another JSON API kit.

## Installation

```sh
composer require joust/joust
```

## Usage

Early stage of development, everything is in flux!

## Documentation

- [Routes](docs/routes.md): define routes, route lists, and access matched path parameters.
- [Route discovery and caching](docs/cache.md): register handler routes with `AsRoute` and cache discovered routes.
- [PSR-15 routing](docs/psr-15.md): use Joust as middleware or a terminal request handler.
- [JSON responses](docs/responses.md): create JSON and problem responses and configure defaults.

## Demo

Run the todo API demo with PHP's built-in server:

```sh
composer install
composer demo
```

Open <http://localhost:8081> to try the API from the clickable home page. Todo data persists between requests in a JSON file in the system temporary directory.

The API includes `GET /todos`, `GET /todos/:id`, `POST /todos`, and `PATCH /todos/:id/complete`. The home page also demonstrates validation, not-found responses, malformed JSON, unrouted requests, and errata's exception-to-problem handling.

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
