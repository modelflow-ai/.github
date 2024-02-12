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

namespace ModelflowAi\Core\Tests\Unit\Request\Message;

use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Request\Message\TextPart;
use PHPUnit\Framework\TestCase;

class TextPartTest extends TestCase
{
    public function testConstruct(): void
    {
        $content = 'Test content';

        $textPart = new TextPart($content);

        $this->assertSame($content, $textPart->text);
    }

    public function testEnhanceMessage(): void
    {
        $content = 'Test content';
        $textPart = new TextPart($content);

        $result = [
            'role' => AIChatMessageRoleEnum::USER->value,
            'content' => '',
        ];
        $expectedResult = [
            'role' => AIChatMessageRoleEnum::USER->value,
            'content' => $content,
        ];

        $this->assertSame($expectedResult, $textPart->enhanceMessage($result));
    }
}
