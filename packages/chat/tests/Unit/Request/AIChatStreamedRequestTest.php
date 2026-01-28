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

namespace ModelflowAi\Chat\Tests\Unit\Request;

use ModelflowAi\Chat\Request\AIChatMessageCollection;
use ModelflowAi\Chat\Request\AIChatStreamedRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Response\AIChatResponseStream;
use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;
use ModelflowAi\DecisionTree\Criteria\FeatureCriteria;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AIChatStreamedRequestTest extends TestCase
{
    use ProphecyTrait;

    public function testConstructorWithStreamed(): void
    {
        $requestHandler = static fn ($request) => null;
        $request = new AIChatStreamedRequest(new AIChatMessageCollection(), new CriteriaCollection(), [], [], [], $requestHandler);

        $this->assertTrue($request->matches([FeatureCriteria::STREAM]));
        $this->assertTrue($request->isStreamed());
    }

    public function testExecute(): void
    {
        $message1 = new AIChatMessage(AIChatMessageRoleEnum::USER, 'Test content 1');
        $message2 = new AIChatMessage(AIChatMessageRoleEnum::USER, 'Test content 2');
        $criteriaCollection = new CriteriaCollection();

        $requestHandler = static fn ($request) => new AIChatResponseStream(
            $request,
            new \ArrayIterator([]),
        );
        $request = new AIChatStreamedRequest(
            new AIChatMessageCollection($message1, $message2),
            $criteriaCollection,
            [],
            [],
            [],
            $requestHandler,
        );

        $response = $request->execute();

        $this->assertInstanceOf(AIChatResponseStream::class, $response);
        $this->assertSame($request, $response->getRequest());
    }
}
