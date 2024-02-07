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

use ModelflowAi\Core\Request\AITextRequest;
use ModelflowAi\Core\Request\Criteria\AIRequestCriteriaCollection;
use ModelflowAi\Core\Request\Criteria\CapabilityRequirement;
use ModelflowAi\Core\Request\Criteria\PrivacyRequirement;
use ModelflowAi\Core\Response\AITextResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AITextRequestTest extends TestCase
{
    use ProphecyTrait;

    public function testExecute(): void
    {
        $criteriaCollection = new AIRequestCriteriaCollection();

        $requestHandler = fn ($request) => new AITextResponse($request, 'Response content 1');
        $request = new AITextRequest('Test content 1', $criteriaCollection, $requestHandler);

        $response = $request->execute();

        $this->assertInstanceOf(AITextResponse::class, $response);
        $this->assertSame($request, $response->getRequest());
        $this->assertSame('Response content 1', $response->getText());
    }

    public function testMatches(): void
    {
        $criteria1 = CapabilityRequirement::BASIC;
        $criteria2 = PrivacyRequirement::HIGH;
        $criteriaCollection = new AIRequestCriteriaCollection([$criteria1, $criteria2]);

        $requestHandler = fn () => null;
        $request = new AITextRequest('Test content 1', $criteriaCollection, $requestHandler);

        $this->assertTrue($request->matches(CapabilityRequirement::BASIC));
        $this->assertTrue($request->matches(PrivacyRequirement::LOW));
    }
}