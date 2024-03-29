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

namespace ModelflowAi\ApiClient\Tests\Unit\Transport\Response;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\ApiClient\Tests\DataFixtures;
use ModelflowAi\ApiClient\Transport\Response\ObjectResponse;
use PHPUnit\Framework\TestCase;

final class ObjectResponseTest extends TestCase
{
    public function testConstructor(): void
    {
        $meta = MetaInformation::from(['Content-Type' => 'application/json']);

        $objectResponse = new ObjectResponse(DataFixtures::CHAT_CREATE_RESPONSE, $meta);

        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE, $objectResponse->data);
        $this->assertSame($meta, $objectResponse->meta);
    }
}
