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

namespace ModelflowAi\Embeddings\Tests\Unit\Adapter\Request;

use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use PHPUnit\Framework\TestCase;

class EmbedRequestTest extends TestCase
{
    public function testConstructWithSingleText(): void
    {
        $request = new EmbedRequest(['test text']);

        $this->assertSame(['test text'], $request->getTexts());
        $this->assertSame(1, $request->count());
    }

    public function testConstructWithMultipleTexts(): void
    {
        $texts = ['first text', 'second text', 'third text'];
        $request = new EmbedRequest($texts);

        $this->assertSame($texts, $request->getTexts());
        $this->assertSame(3, $request->count());
    }

    public function testConstructWithEmptyArrayThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('EmbedRequest requires at least one text to embed.');

        new EmbedRequest([]);
    }
}
