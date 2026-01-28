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

namespace ModelflowAi\Completion\Tests\Unit\Request;

use ModelflowAi\Completion\Request\AICompletionRequest;
use ModelflowAi\Completion\Response\AICompletionResponse;
use ModelflowAi\DecisionTree\Criteria\CapabilityCriteria;
use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;
use ModelflowAi\DecisionTree\Criteria\PrivacyCriteria;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AICompletionRequestTest extends TestCase
{
    use ProphecyTrait;

    public function testExecute(): void
    {
        $criteriaCollection = new CriteriaCollection();

        $requestHandler = static fn ($request) => new AICompletionResponse($request, 'Response content 1');
        $request = new AICompletionRequest('Test content 1', $criteriaCollection, [], $requestHandler);

        $response = $request->execute();

        $this->assertInstanceOf(AICompletionResponse::class, $response);
        $this->assertSame($request, $response->getRequest());
        $this->assertSame('Response content 1', $response->getContent());
    }

    public function testMatches(): void
    {
        $criteria1 = CapabilityCriteria::BASIC;
        $criteria2 = PrivacyCriteria::LOW;
        $criteriaCollection = new CriteriaCollection([$criteria1, $criteria2]);

        $requestHandler = static fn () => null;
        $request = new AICompletionRequest('Test content 1', $criteriaCollection, [], $requestHandler);

        $this->assertTrue($request->matches([CapabilityCriteria::BASIC]));
        $this->assertTrue($request->matches([PrivacyCriteria::HIGH]));
    }

    public function testOptions(): void
    {
        $criteria1 = CapabilityCriteria::BASIC;
        $criteria2 = PrivacyCriteria::HIGH;
        $criteriaCollection = new CriteriaCollection([$criteria1, $criteria2]);

        $requestHandler = static fn () => null;
        $request = new AICompletionRequest('Test content 1', $criteriaCollection, ['format' => 'json'], $requestHandler);

        $this->assertSame('json', $request->getOption('format'));
    }
}
