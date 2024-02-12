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
use ModelflowAi\Core\Request\Message\ImageBase64Part;
use PHPUnit\Framework\TestCase;

class ImageBase64PartTest extends TestCase
{
    public function testCreate(): void
    {
        $path = __DIR__ . '/test_image.jpg'; // Replace with the path to a test image file
        $content = \base64_encode((string) \file_get_contents($path));

        $imagePart = ImageBase64Part::create($path);

        $this->assertSame($content, $imagePart->content);
    }

    public function testEnhanceMessage(): void
    {
        $content = 'iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg=='; // This is a 1x1 pixel white image in base64 format
        $imagePart = new ImageBase64Part($content);

        $result = [
            'role' => AIChatMessageRoleEnum::USER->value,
            'content' => '',
        ];
        $expectedResult = [
            'role' => AIChatMessageRoleEnum::USER->value,
            'content' => '',
            'images' => [$content],
        ];

        $this->assertSame($expectedResult, $imagePart->enhanceMessage($result));
    }
}
