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
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\PromptTemplate\Chat\AIChatMessage;
use ModelflowAi\PromptTemplate\Chat\AIChatMessageRoleEnum;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AIChatResponseTest extends TestCase
{
    use ProphecyTrait;

    public function testGetMessage(): void
    {
        $request = $this->prophesize(AIChatRequest::class);

        $message = new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Test content');
        $response = new AIChatResponse($request->reveal(), $message);

        $this->assertSame($message, $response->getMessage());
    }

    public function testGetRequest(): void
    {
        $request = $this->prophesize(AIChatRequest::class);

        $message = new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Test content');
        $response = new AIChatResponse($request->reveal(), $message);

        $this->assertSame($request->reveal(), $response->getRequest());
    }
}
