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

namespace ModelflowAi\Mistral\Tests\Unit\Responses\Chat;

use ModelflowAi\Mistral\Responses\Chat\ContentChunks;
use PHPUnit\Framework\TestCase;

final class ContentChunksTest extends TestCase
{
    public function testToTextKeepsAString(): void
    {
        $this->assertSame('Lorem Ipsum', ContentChunks::toText('Lorem Ipsum'));
        $this->assertNull(ContentChunks::toText(null));
    }

    public function testToTextDropsTheThinkingTrace(): void
    {
        $content = [
            ['type' => 'thinking', 'thinking' => [['type' => 'text', 'text' => 'Let me think.']]],
            ['type' => 'text', 'text' => 'Lorem Ipsum'],
        ];

        $this->assertSame('Lorem Ipsum', ContentChunks::toText($content));
    }

    public function testToTextJoinsTheAnswerChunks(): void
    {
        $content = [
            ['type' => 'text', 'text' => 'Lorem '],
            ['type' => 'text'],
            'Ipsum',
        ];

        $this->assertSame('Lorem Ipsum', ContentChunks::toText($content));
    }

    public function testToTextReturnsAnEmptyStringWhileOnlyThinking(): void
    {
        $content = [
            ['type' => 'thinking', 'thinking' => [['type' => 'text', 'text' => 'Still thinking.']]],
        ];

        $this->assertSame('', ContentChunks::toText($content));
    }
}
