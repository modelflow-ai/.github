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

use ModelflowAi\Mistral\Responses\Chat\CreateStreamedResponseToolCall;
use ModelflowAi\Mistral\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;

final class CreateStreamedResponseToolCallTest extends TestCase
{
    public function testFrom(): void
    {
        $attributes = DataFixtures::CHAT_CREATE_STREAMED_RESPONSES_WITH_TOOLS[0]['choices'][0]['delta']['tool_calls'][0];

        $instance = CreateStreamedResponseToolCall::from(0, $attributes);

        $this->assertInstanceOf(CreateStreamedResponseToolCall::class, $instance);
        $this->assertSame($attributes['id'], $instance->id);
        $this->assertSame($attributes['type'], $instance->type);
        $this->assertSame($attributes['function']['name'], $instance->function->name);
        $this->assertSame($attributes['function']['arguments'], $instance->function->arguments);
    }
}
