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

use ModelflowAi\Mistral\Responses\Chat\CreateResponseToolCall;
use ModelflowAi\Mistral\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;

final class CreateResponseToolCallTest extends TestCase
{
    public function testFrom(): void
    {
        $attributes = DataFixtures::CHAT_CREATE_RESPONSE_WITH_TOOLS['choices'][0]['message']['tool_calls'][0];

        $instance = CreateResponseToolCall::from(0, $attributes);

        $this->assertSame($attributes['id'], $instance->id);
        $this->assertSame($attributes['type'], $instance->type);
        $this->assertSame($attributes['function']['name'], $instance->function->name);
        $this->assertSame($attributes['function']['arguments'], $instance->function->arguments);
    }

    public function testFromWithoutIdAndType(): void
    {
        $attributes = DataFixtures::CHAT_CREATE_RESPONSE_WITH_TOOLS['choices'][0]['message']['tool_calls'][0];
        unset($attributes['id']);
        unset($attributes['type']);

        $instance = CreateResponseToolCall::from(0, $attributes);

        $this->assertSame('0', $instance->id);
        $this->assertSame('function', $instance->type);
        $this->assertSame($attributes['function']['name'], $instance->function->name);
        $this->assertSame($attributes['function']['arguments'], $instance->function->arguments);
    }
}
