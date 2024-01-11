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

namespace ModelflowAi\Mistral\Tests\Unit\Transport;

use ModelflowAi\Mistral\Tests\DataFixtures;
use ModelflowAi\Mistral\Transport\Enums\ContentType;
use ModelflowAi\Mistral\Transport\Enums\Method;
use ModelflowAi\Mistral\Transport\Payload;
use PHPUnit\Framework\TestCase;

final class PayloadTest extends TestCase
{
    public function testCreate(): void
    {
        $payload = Payload::create('chat/response', DataFixtures::CHAT_CREATE_REQUEST);

        $this->assertInstanceOf(Payload::class, $payload);
        $this->assertSame(Method::POST, $payload->method);
        $this->assertSame(ContentType::JSON, $payload->contentType);
        $this->assertSame('chat/response', $payload->resourceUri->uri);
        $this->assertSame(DataFixtures::CHAT_CREATE_REQUEST, $payload->parameters);
    }
}
