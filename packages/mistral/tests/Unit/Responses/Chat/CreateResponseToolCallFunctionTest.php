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

use ModelflowAi\Mistral\Responses\Chat\CreateResponseToolCallFunction;
use ModelflowAi\Mistral\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;

final class CreateResponseToolCallFunctionTest extends TestCase
{
    public function testFrom(): void
    {
        $attributes = DataFixtures::CHAT_CREATE_RESPONSE_WITH_TOOLS['choices'][0]['message']['tool_calls'][0]['function'];

        $instance = CreateResponseToolCallFunction::from($attributes);

        $this->assertSame($attributes['name'], $instance->name);
        $this->assertSame($attributes['arguments'], $instance->arguments);
    }
}
