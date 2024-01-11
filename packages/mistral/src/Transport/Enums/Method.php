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

namespace ModelflowAi\Mistral\Transport\Enums;

enum Method: string
{
    case GET = 'GET';
    case POST = 'POST';
    case DELETE = 'DELETE';
}
