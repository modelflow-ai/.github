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

use ModelflowAi\Mistral\Responses\Chat\CreateStreamedResponseToolCallFunction;
use ModelflowAi\Mistral\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;

final class CreateStreamedResponseToolCallFunctionTest extends TestCase
{
    public function testFrom(): void
    {
        $attributes = DataFixtures::CHAT_CREATE_STREAMED_RESPONSES_WITH_TOOLS[0]['choices'][0]['delta']['tool_calls'][0]['function'];

        $instance = CreateStreamedResponseToolCallFunction::from($attributes);

        $this->assertInstanceOf(CreateStreamedResponseToolCallFunction::class, $instance);
        $this->assertSame($attributes['name'], $instance->name);
        $this->assertSame($attributes['arguments'], $instance->arguments);
    }
}
