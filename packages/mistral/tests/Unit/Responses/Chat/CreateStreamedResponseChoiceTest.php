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

use ModelflowAi\Mistral\Responses\Chat\CreateStreamedResponseChoice;
use ModelflowAi\Mistral\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;

final class CreateStreamedResponseChoiceTest extends TestCase
{
    public function testFrom(): void
    {
        $attributes = DataFixtures::CHAT_CREATE_STREAMED_RESPONSES[0]['choices'][0];

        $choice = CreateStreamedResponseChoice::from($attributes);

        $this->assertInstanceOf(CreateStreamedResponseChoice::class, $choice);
        $this->assertSame($attributes['index'], $choice->index);
        $this->assertSame($attributes['delta']['role'], $choice->delta->role);
        $this->assertSame($attributes['delta']['content'], $choice->delta->content);
        $this->assertSame($attributes['finish_reason'], $choice->finishReason);
    }
}
