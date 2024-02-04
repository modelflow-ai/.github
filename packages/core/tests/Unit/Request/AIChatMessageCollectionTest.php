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

namespace ModelflowAi\Core\Tests\Unit\Request;

use ModelflowAi\Core\Request\AIChatMessageCollection;
use ModelflowAi\PromptTemplate\Chat\AIChatMessage;
use ModelflowAi\PromptTemplate\Chat\AIChatMessageRoleEnum;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AIChatMessageCollectionTest extends TestCase
{
    use ProphecyTrait;

    public function testToArray(): void
    {
        $message1 = new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Test content 1');
        $message2 = new AIChatMessage(AIChatMessageRoleEnum::USER, 'Test content 2');

        $collection = new AIChatMessageCollection($message1, $message2);

        $expected = [
            [
                'role' => 'assistant',
                'content' => 'Test content 1',
            ],
            [
                'role' => 'user',
                'content' => 'Test content 2',
            ],
        ];

        $this->assertSame($expected, $collection->toArray());
    }
}
