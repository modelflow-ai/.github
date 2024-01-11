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

namespace ModelflowAi\Mistral;

enum Model: string
{
    case TINY = 'mistral-tiny';
    case SMALL = 'mistral-small';
    case MEDIUM = 'mistral-medium';
}
