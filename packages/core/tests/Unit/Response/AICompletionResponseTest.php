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

namespace ModelflowAi\Core\Tests\Unit\Response;

use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\AICompletionRequest;
use ModelflowAi\Core\Response\AICompletionResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AICompletionResponseTest extends TestCase
{
    use ProphecyTrait;

    public function testGetMessage(): void
    {
        $request = $this->prophesize(AIChatRequest::class);

        $response = new AICompletionResponse($request->reveal(), 'Test content');

        $this->assertSame('Test content', $response->getContent());
    }

    public function testGetRequest(): void
    {
        $request = $this->prophesize(AICompletionRequest::class);

        $response = new AICompletionResponse($request->reveal(), 'Test content');

        $this->assertSame($request->reveal(), $response->getRequest());
    }
}
