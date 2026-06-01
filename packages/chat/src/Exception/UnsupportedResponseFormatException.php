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

namespace ModelflowAi\Chat\Exception;

use ModelflowAi\Chat\Request\ResponseFormat\ResponseFormatInterface;

final class UnsupportedResponseFormatException extends \RuntimeException
{
    public static function forAdapter(ResponseFormatInterface $responseFormat, object $adapter): self
    {
        return new self(\sprintf(
            'The response format "%s" is not supported by adapter "%s".',
            $responseFormat->getType(),
            $adapter::class,
        ));
    }
}
