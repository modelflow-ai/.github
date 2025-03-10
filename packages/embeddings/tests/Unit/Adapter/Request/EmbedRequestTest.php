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
    public function testConstruct(): void
    {
        $request = new EmbedRequest('test text');

        $this->assertSame('test text', $request->getText());
    }
}
