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

namespace ModelflowAi\Mistral\Tests\Unit\Transport\ValueObjects;

use ModelflowAi\Mistral\Transport\ValueObjects\ResourceUri;
use PHPUnit\Framework\TestCase;

final class ResourceUriTest extends TestCase
{
    public function testConstructor(): void
    {
        $uri = 'chat/completions';
        $resourceUri = new ResourceUri($uri);

        $this->assertSame($uri, $resourceUri->uri);
        $this->assertSame($uri, $resourceUri->__toString());
    }
}
