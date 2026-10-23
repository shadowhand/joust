<?php

declare(strict_types=1);

namespace Joust;

use IteratorAggregate;

/**
 * A list of routes.
 *
 * @api
 * @extends IteratorAggregate<int, Route>
 */
interface RouteList extends IteratorAggregate {}
