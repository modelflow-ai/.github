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

use ModelflowAi\Mistral\Responses\CreateResponseChoice;
use ModelflowAi\Mistral\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;

final class CreateResponseChoiceTest extends TestCase
{
    public function testFrom(): void
    {
        $choiceData = DataFixtures::CHAT_CREATE_RESPONSE['choices'][0];

        $choice = CreateResponseChoice::from($choiceData);

        $this->assertInstanceOf(CreateResponseChoice::class, $choice);
        $this->assertSame($choiceData['index'], $choice->index);
        $this->assertSame($choiceData['message']['role'], $choice->message->role);
        $this->assertSame($choiceData['message']['content'], $choice->message->content);
        $this->assertSame($choiceData['finish_reason'], $choice->finishReason);
    }
}
