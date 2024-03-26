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

namespace ModelflowAi\mistral\tests\Unit\Responses\Chat;

use ModelflowAi\Mistral\Responses\Chat\CreateStreamedResponseDelta;
use ModelflowAi\Mistral\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;

final class CreateStreamedResponseDeltaTest extends TestCase
{
    public function testFrom(): void
    {
        $attributes = DataFixtures::CHAT_CREATE_STREAMED_RESPONSES[0]['choices'][0]['delta'];

        $message = CreateStreamedResponseDelta::from($attributes);

        $this->assertInstanceOf(CreateStreamedResponseDelta::class, $message);
        $this->assertSame($attributes['role'], $message->role);
        $this->assertSame($attributes['content'], $message->content);
    }

    public function testFromWithTools(): void
    {
        $attributes = DataFixtures::CHAT_CREATE_STREAMED_RESPONSES_WITH_TOOLS[0]['choices'][0]['delta'];

        $message = CreateStreamedResponseDelta::from($attributes);

        $this->assertInstanceOf(CreateStreamedResponseDelta::class, $message);
        $this->assertSame($attributes['role'], $message->role);
        $this->assertSame($attributes['content'], $message->content);
        $this->assertCount(\count($attributes['tool_calls']), $message->toolCalls);
        $this->assertSame($attributes['tool_calls'][0]['id'], $message->toolCalls[0]->id);
        $this->assertSame($attributes['tool_calls'][0]['type'], $message->toolCalls[0]->type);
        $this->assertSame($attributes['tool_calls'][0]['function']['name'], $message->toolCalls[0]->function->name);
        $this->assertSame($attributes['tool_calls'][0]['function']['arguments'], $message->toolCalls[0]->function->arguments);
    }
}
