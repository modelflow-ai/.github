<?php

declare(strict_types=1);

/*
 * This file is part of the Modelflow AI package.
 *
 * (c) Johannes Wachter <johannes@sulu.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ModelflowAi\ApiClient\Transport\Response;

use ModelflowAi\ApiClient\Responses\MetaInformation;

abstract readonly class Response
{
    public function __construct(
        public MetaInformation $meta,
    ) {
    }
}
