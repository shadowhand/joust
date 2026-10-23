# Changelog

All notable changes to this project are documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/),
and this project adheres to [Semantic Versioning](https://semver.org/).

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
