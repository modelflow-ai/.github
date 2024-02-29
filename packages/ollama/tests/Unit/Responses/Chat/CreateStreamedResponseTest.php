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

namespace ModelflowAi\Ollama\Tests\Unit\Responses\Chat;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\Ollama\Responses\Chat\CreateStreamedResponse;
use ModelflowAi\Ollama\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;

final class CreateStreamedResponseTest extends TestCase
{
    public function testFrom(): void
    {
        $instance = CreateStreamedResponse::from(0, DataFixtures::CHAT_CREATE_STREAMED_RESPONSES[0], MetaInformation::from([]));

        $this->assertInstanceOf(CreateStreamedResponse::class, $instance);
        $this->assertSame(DataFixtures::CHAT_CREATE_STREAMED_RESPONSES[0]['model'], $instance->model);
        $this->assertSame(
            (new \DateTimeImmutable(DataFixtures::CHAT_CREATE_STREAMED_RESPONSES[0]['created_at']))->getTimestamp(),
            $instance->createdAt,
        );
        $this->assertSame(0, $instance->index);
        $this->assertSame(DataFixtures::CHAT_CREATE_STREAMED_RESPONSES[0]['message']['role'], $instance->message->role);
        $this->assertSame(DataFixtures::CHAT_CREATE_STREAMED_RESPONSES[0]['message']['content'], $instance->message->delta);
        $this->assertSame(DataFixtures::CHAT_CREATE_STREAMED_RESPONSES[0]['done'], $instance->done);
        $this->assertInstanceOf(MetaInformation::class, $instance->meta);
    }
}
