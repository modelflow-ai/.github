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

namespace ModelflowAi\Chat\Tests\Unit\Exception;

use ModelflowAi\Chat\Exception\UnsupportedResponseFormatException;
use ModelflowAi\Chat\Request\ResponseFormat\JsonSchemaResponseFormat;
use PHPUnit\Framework\TestCase;

final class UnsupportedResponseFormatExceptionTest extends TestCase
{
    public function testForAdapter(): void
    {
        $exception = UnsupportedResponseFormatException::forAdapter(
            new JsonSchemaResponseFormat([
                'type' => 'object',
                'properties' => [],
            ]),
            new \stdClass(),
        );

        $this->assertSame(
            'The response format "json_schema" is not supported by adapter "stdClass".',
            $exception->getMessage(),
        );
    }
}
