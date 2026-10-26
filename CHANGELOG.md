# Changelog

All notable changes to this project are documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added

- `Finder\RouteFinderSettings` and `Cache\RouteCacheSettings` to configure route discovery and caching.
- `App::setFallback()` to replace the fallback request handler.

### Changed

- Added `Handler` suffix to request handlers.
- Added `Middleware` suffix to request middleware.
- `AsRoute` accepts URL pattern components (`baseUrl`, `scheme`, `username`, `password`, `host`, `path`, `query`, and
  `fragment`) instead of a `UrlPattern` instance or a path string.
- `RouteReader` builds URL patterns from route attributes and accepts an optional base URL. A route's own `baseUrl`
  takes precedence over the base URL configured for the reader.
- `RouteCache` reads and writes the cache file configured by `RouteCacheSettings` instead of taking a file path on
  each call.
- `App::liveRouter()` and `App::cachedRouter()` replace `collectRoutes()` and `cachedRoutes()`. They resolve a
  `RouteFinder` or `RouteCache` from the container, so discovery and cache settings are configured there.
- `App` no longer accepts a fallback handler in its constructor; use `setFallback()` instead.
- Settings classes accept `null` values for their constructor arguments, applying defaults and validating non-null
  values at runtime.
- Settings default file paths to the Composer root package directory, read through `composer-runtime-api`.
- Replaced `wyrihaximus/list-classes-in-directory` with `composer/class-map-generator` for route discovery.

### Removed

- `Joust\Locator` namespace, `RouteLocator`, and the `collectRoutes()` and `cachedRoutes()` application methods.

## [0.3.0] - 2026-10-23

### Added

- `App` as a full application runner. It runs middleware in the order added, then runs a fallback handler.
  It takes a PSR-11 container and (optionally) a request handler to use as the fallback. When no fallback is given,
  it uses `Handler\NotFound` to generate a HTTP 404 page. `App` also provides convenience methods for adding routing,
  from a cache or discovered at runtime.

### Changed

- Refactored most functionality for better separation of concerns.
- Added runtime checks for maximum type safety.

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

[Unreleased]: https://github.com/shadowhand/joust/compare/0.3.0...main
[0.3.0]: https://github.com/shadowhand/joust/compare/0.2.1...0.3.0
[0.2.1]: https://github.com/shadowhand/joust/compare/0.2.0...0.2.1
[0.2.0]: https://github.com/shadowhand/joust/compare/0.1.0...0.2.0
[0.1.0]: https://github.com/shadowhand/joust/releases/tag/0.1.0
