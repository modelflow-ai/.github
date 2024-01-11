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

namespace ModelflowAi\Mistral\Transport;

use ModelflowAi\Mistral\Transport\Response\ObjectResponse;
use ModelflowAi\Mistral\Transport\Response\TextResponse;

interface TransportInterface
{
    public function requestText(Payload $payload): TextResponse;

    public function requestObject(Payload $payload): ObjectResponse;
}
