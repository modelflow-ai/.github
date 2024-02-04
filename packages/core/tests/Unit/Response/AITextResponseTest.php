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
use ModelflowAi\Core\Request\AITextRequest;
use ModelflowAi\Core\Response\AITextResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AITextResponseTest extends TestCase
{
    use ProphecyTrait;

    public function testGetMessage(): void
    {
        $request = $this->prophesize(AIChatRequest::class);

        $response = new AITextResponse($request->reveal(), 'Test content');

        $this->assertSame('Test content', $response->getText());
    }

    public function testGetRequest(): void
    {
        $request = $this->prophesize(AITextRequest::class);

        $response = new AITextResponse($request->reveal(), 'Test content');

        $this->assertSame($request->reveal(), $response->getRequest());
    }
}
