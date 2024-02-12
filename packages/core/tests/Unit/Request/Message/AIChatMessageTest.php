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

use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Request\Message\ImageBase64Part;
use ModelflowAi\Core\Request\Message\TextPart;
use PHPUnit\Framework\TestCase;

class AIChatMessageTest extends TestCase
{
    public function testConstructAndToArrayWithStringContent(): void
    {
        $role = AIChatMessageRoleEnum::USER;
        $content = 'Test content';

        $message = new AIChatMessage($role, $content);

        $expectedArray = [
            'role' => $role->value,
            'content' => $content,
        ];

        $this->assertSame($expectedArray, $message->toArray());
    }

    public function testConstructAndToArrayWithMessagePartContent(): void
    {
        $role = AIChatMessageRoleEnum::USER;
        $textPart = new TextPart('Test content');

        $message = new AIChatMessage($role, $textPart);

        $expectedArray = [
            'role' => $role->value,
            'content' => $textPart->text,
        ];

        $this->assertSame($expectedArray, $message->toArray());
    }

    public function testConstructAndToArrayWithImageBase64Content(): void
    {
        $role = AIChatMessageRoleEnum::USER;
        $imagePart = new ImageBase64Part('iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg=='); // This is a 1x1 pixel white image in base64 format

        $message = new AIChatMessage($role, $imagePart);

        $expectedArray = [
            'role' => $role->value,
            'content' => '',
            'images' => [$imagePart->content],
        ];

        $this->assertSame($expectedArray, $message->toArray());
    }

    public function testConstructAndToArrayWithTextAndImageContent(): void
    {
        $role = AIChatMessageRoleEnum::USER;
        $textPart = new TextPart('Test content');
        $imagePart = new ImageBase64Part('iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg=='); // This is a 1x1 pixel white image in base64 format

        $message = new AIChatMessage($role, [$textPart, $imagePart]);

        $expectedArray = [
            'role' => $role->value,
            'content' => $textPart->text,
            'images' => [$imagePart->content],
        ];

        $this->assertSame($expectedArray, $message->toArray());
    }
}
