--TEST--
SapiEmitter fails when headers were already sent
--FILE--
<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Joust\Response\SapiEmitter;
use Nyholm\Psr7\Response;

echo "early\n";

try {
    new SapiEmitter()->emit(new Response());
} catch (RuntimeException $e) {
    echo $e->getMessage();
}
--EXPECTF--
early
Unable to emit response: headers already sent in %s on line %d.
