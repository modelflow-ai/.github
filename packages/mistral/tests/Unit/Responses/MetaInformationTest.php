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

namespace ModelflowAi\Mistral\Tests\Unit\Responses;

use ModelflowAi\Mistral\Responses\MetaInformation;
use PHPUnit\Framework\TestCase;

final class MetaInformationTest extends TestCase
{
    public function testFrom(): void
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        $meta = MetaInformation::from($headers);

        $this->assertInstanceOf(MetaInformation::class, $meta);
        $this->assertSame($headers, $meta->headers);
    }
}
