<?php

declare(strict_types=1);

namespace Joust\Demo\Routes;

use Joust\Attribute\AsRoute;
use Joust\Method;
use Override;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[AsRoute(Method::Get, '/')]
final readonly class Home implements RequestHandlerInterface
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
    ) {}

    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $html = <<<'HTML'
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <title>Joust Todo Demo</title>
                <style>
                    body { font-family: sans-serif; max-width: 46rem; margin: 3rem auto; padding: 0 1rem; }
                    h1 small { font-weight: normal; color: #666; }
                    .endpoint { display: block; width: 100%; text-align: left; margin: 0.25rem 0; padding: 0.6rem 0.9rem;
                        font-family: ui-monospace, monospace; font-size: 0.95rem; border: 1px solid #ccc;
                        border-radius: 0.4rem; background: #fafafa; cursor: pointer; }
                    .endpoint:hover { background: #eef; }
                    .method { display: inline-block; min-width: 3.6rem; font-weight: bold; }
                    #output { margin-top: 1.5rem; padding: 1rem; border: 1px solid #ccc; border-radius: 0.4rem;
                        background: #111; color: #cfe; white-space: pre; overflow-x: auto; min-height: 3rem;
                        font-family: ui-monospace, monospace; }
                </style>
            </head>
            <body>
                <h1>Joust Todo Demo <small>a todo API built with joust</small></h1>
                <p>Click an endpoint to send the request and inspect the raw response.</p>

                <button class="endpoint" data-method="GET" data-path="/"><span class="method">GET</span>/</button>
                <button class="endpoint" data-method="GET" data-path="/todos"><span class="method">GET</span>/todos</button>
                <button class="endpoint" data-method="GET" data-path="/todos/1"><span class="method">GET</span>/todos/1</button>
                <button class="endpoint" data-method="GET" data-path="/todos/999"><span class="method">GET</span>/todos/999 <em>(unknown id &rarr; 404)</em></button>
                <button class="endpoint" data-method="POST" data-path="/todos" data-body='{"title":"Buy milk"}'><span class="method">POST</span>/todos <em>(&quot;Buy milk&quot;)</em></button>
                <button class="endpoint" data-method="POST" data-path="/todos" data-body='not json'><span class="method">POST</span>/todos <em>(invalid JSON &rarr; 400)</em></button>
                <button class="endpoint" data-method="POST" data-path="/todos" data-body='{}'><span class="method">POST</span>/todos <em>(missing title &rarr; 422)</em></button>
                <button class="endpoint" data-method="PATCH" data-path="/todos/3/complete"><span class="method">PATCH</span>/todos/3/complete</button>
                <button class="endpoint" data-method="PATCH" data-path="/todos/999/complete"><span class="method">PATCH</span>/todos/999/complete <em>(unknown id &rarr; 404)</em></button>
                <button class="endpoint" data-method="GET" data-path="/crash"><span class="method">GET</span>/crash <em>(unhandled error &rarr; 500)</em></button>
                <button class="endpoint" data-method="GET" data-path="/nope"><span class="method">GET</span>/nope <em>(unrouted &rarr; 404)</em></button>

                <div id="output">Click a button above to see the response.</div>

                <script>
                    for (const button of document.querySelectorAll('.endpoint')) {
                        button.addEventListener('click', async () => {
                            const output = document.getElementById('output');
                            output.textContent = 'Loading…';
                            try {
                                const options = {method: button.dataset.method};
                                if (button.dataset.body) {
                                    options.headers = {'Content-Type': 'application/json'};
                                    options.body = button.dataset.body;
                                }
                                const response = await fetch(button.dataset.path, options);
                                const body = await response.text();
                                output.textContent = response.status + ' ' + response.statusText + '\n\n' + body;
                            } catch (error) {
                                output.textContent = 'Request failed: ' + error;
                            }
                        });
                    }
                </script>
            </body>
            </html>
            HTML;

        $response = $this->responseFactory->createResponse(200)->withHeader('Content-Type', 'text/html; charset=utf-8');

        return $response->withBody($this->streamFactory->createStream($html));
    }
}
