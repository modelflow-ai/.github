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

namespace ModelflowAi\Core\Tests\Unit\Request;

use ModelflowAi\Core\Request\AIChatMessageCollection;
use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\Criteria\AIRequestCriteriaCollection;
use ModelflowAi\Core\Request\Criteria\CapabilityRequirement;
use ModelflowAi\Core\Request\Criteria\PrivacyRequirement;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\PromptTemplate\Chat\AIChatMessage;
use ModelflowAi\PromptTemplate\Chat\AIChatMessageRoleEnum;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AIChatRequestTest extends TestCase
{
    use ProphecyTrait;

    public function testExecute(): void
    {
        $message1 = new AIChatMessage(AIChatMessageRoleEnum::USER, 'Test content 1');
        $message2 = new AIChatMessage(AIChatMessageRoleEnum::USER, 'Test content 2');
        $criteriaCollection = new AIRequestCriteriaCollection();

        $requestHandler = fn ($request) => new AIChatResponse($request, new AIChatMessage(AIChatMessageRoleEnum::ASSISTANT, 'Response content 1'));
        $request = new AIChatRequest(new AIChatMessageCollection($message1, $message2), $criteriaCollection, $requestHandler);

        $response = $request->execute();

        $this->assertInstanceOf(AIChatResponse::class, $response);
        $this->assertSame($request, $response->getRequest());
        $this->assertSame('Response content 1', $response->getMessage()->content);
    }

    public function testMatches(): void
    {
        $criteria1 = CapabilityRequirement::BASIC;
        $criteria2 = PrivacyRequirement::HIGH;
        $criteriaCollection = new AIRequestCriteriaCollection([$criteria1, $criteria2]);

        $requestHandler = fn () => null;
        $request = new AIChatRequest(new AIChatMessageCollection(), $criteriaCollection, $requestHandler);

        $this->assertTrue($request->matches(CapabilityRequirement::BASIC));
        $this->assertTrue($request->matches(PrivacyRequirement::LOW));
    }
}
