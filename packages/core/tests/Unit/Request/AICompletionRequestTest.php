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

use ModelflowAi\Core\Request\AICompletionRequest;
use ModelflowAi\Core\Request\Criteria\AIRequestCriteriaCollection;
use ModelflowAi\Core\Request\Criteria\CapabilityCriteria;
use ModelflowAi\Core\Request\Criteria\PrivacyCriteria;
use ModelflowAi\Core\Response\AICompletionResponse;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AICompletionRequestTest extends TestCase
{
    use ProphecyTrait;

    public function testExecute(): void
    {
        $criteriaCollection = new AIRequestCriteriaCollection();

        $requestHandler = fn ($request) => new AICompletionResponse($request, 'Response content 1');
        $request = new AICompletionRequest('Test content 1', $criteriaCollection, $requestHandler);

        $response = $request->execute();

        $this->assertInstanceOf(AICompletionResponse::class, $response);
        $this->assertSame($request, $response->getRequest());
        $this->assertSame('Response content 1', $response->getContent());
    }

    public function testMatches(): void
    {
        $criteria1 = CapabilityCriteria::BASIC;
        $criteria2 = PrivacyCriteria::HIGH;
        $criteriaCollection = new AIRequestCriteriaCollection([$criteria1, $criteria2]);

        $requestHandler = fn () => null;
        $request = new AICompletionRequest('Test content 1', $criteriaCollection, $requestHandler);

        $this->assertTrue($request->matches(CapabilityCriteria::BASIC));
        $this->assertTrue($request->matches(PrivacyCriteria::LOW));
    }
}
