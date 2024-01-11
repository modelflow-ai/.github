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

namespace ModelflowAi\Mistral\Tests\Unit\Transport\Response;

use ModelflowAi\Mistral\Responses\MetaInformation;
use ModelflowAi\Mistral\Transport\Response\TextResponse;
use PHPUnit\Framework\TestCase;

final class TextResponseTest extends TestCase
{
    public function testConstructor(): void
    {
        $text = 'Test text';
        $meta = MetaInformation::from(['Content-Type' => 'text/plain']);

        $textResponse = new TextResponse($text, $meta);

        $this->assertSame($text, $textResponse->text);
        $this->assertSame($meta, $textResponse->meta);
    }
}
