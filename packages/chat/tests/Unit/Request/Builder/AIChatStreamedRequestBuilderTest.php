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

namespace ModelflowAi\Chat\Tests\Unit\Request\Builder;

use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Builder\AIChatStreamedRequestBuilder;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AIChatStreamedRequestBuilderTest extends TestCase
{
    use ProphecyTrait;

    public function testBuild(): void
    {
        $builder = new AIChatStreamedRequestBuilder(static fn () => null);

        $request = $builder->build();
        $this->assertInstanceOf(AIChatStreamedRequest::class, $request);
        $this->assertTrue($request->isStreamed());
    }

    public function testExecute(): void
    {
        $mockFunction = function (object $request) {
            $this->assertInstanceOf(AIChatStreamedRequest::class, $request);

            return new AIChatResponseStream($request, new \ArrayIterator([]));
        };

        $builder = new AIChatStreamedRequestBuilder($mockFunction);

        $response = $builder->execute();
        $this->assertInstanceOf(AIChatResponseStream::class, $response);
    }
}
