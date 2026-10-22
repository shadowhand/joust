# Changelog

All notable changes to this project are documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added

- `App`, which runs middleware in the order it is added and then a fallback handler. Middleware can be added as an
  instance or as a class name resolved lazily from the container.
- `App::collectRoutes()` and `App::cacheRoutes()` to discover routes, optionally through a route cache, and add a
  `Router` for them.
- `Pipeline`, which runs a list of middleware before a request handler.
- `NotFoundHandler`, which responds with a not found problem.

### Changed

- `Router` is middleware only. It no longer implements `RequestHandlerInterface` or takes a `JsonResponseFactory`; use
  `NotFoundHandler` as the fallback instead.
- Require `psr/http-message` and `psr/http-server-middleware` directly.

## [0.2.1] - 2026-10-16

### Changed

- Require stable `league/uri` `^7.9.0` instead of the `7.x-dev` alias.

## [0.2.0] - 2026-10-12

### Changed

- Replace `RoutingMiddleware` and `RoutingHandler` with `Router`, which is both PSR-15 middleware and a request handler.
- `AsRoute` builds string patterns as URL paths and accepts an optional base URL.

## [0.1.0] - 2026-10-10

### Added

- Initial release.

[Unreleased]: https://github.com/shadowhand/joust/compare/0.2.1...main
[0.2.1]: https://github.com/shadowhand/joust/compare/0.2.0...0.2.1
[0.2.0]: https://github.com/shadowhand/joust/compare/0.1.0...0.2.0
[0.1.0]: https://github.com/shadowhand/joust/releases/tag/0.1.0
