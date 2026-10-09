# JSON responses

`JsonResponseFactory` creates PSR-7 responses with JSON bodies. It depends on PSR-17 response and stream factories, so provide implementations from your HTTP message stack.

```php
use Joust\Response\JsonResponseFactory;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/** @var ResponseFactoryInterface $responseFactory */
/** @var StreamFactoryInterface $streamFactory */
$json = new JsonResponseFactory($responseFactory, $streamFactory);

$response = $json->respond(201, ['id' => 1, 'title' => 'Write tests']);
```

`respond()` accepts an HTTP status (default `200`), any JSON-encodable value (default `[]`), and an optional content type. By default, the response uses `application/json; charset=utf-8`. When the content type does not already contain `charset`, the factory appends the configured charset.

## Problem responses

Use `problem()` for an `Errata\Problem` document. Joust serializes it as `application/problem+json` and uses the problem's status, defaulting to 500 when no status is set:

```php
use Errata\Http\Client\NotFound;

$response = $json->problem(new NotFound(detail: 'No todo has that id.'));
```

Errata HTTP problem values are documents, not exceptions. Return them through `problem()`; use Errata's `ProblemMiddleware` when you want thrown exceptions converted into problem responses.

## Configure defaults

`JsonResponseSettings` controls the default content type, charset, pretty-print setting, and JSON encoding flags. Pass it as the third constructor argument:

```php
use Joust\Response\JsonResponseFactory;
use Joust\Response\JsonResponseSettings;

$settings = new JsonResponseSettings(
    contentType: 'application/vnd.example+json',
    charset: 'utf-8',
    pretty: true,
    flags: JSON_THROW_ON_ERROR,
);

$json = new JsonResponseFactory($responseFactory, $streamFactory, $settings);
```

These settings apply to ordinary responses. `problem()` uses the `application/problem+json` media type while retaining the configured charset.
