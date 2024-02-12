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
use ModelflowAi\Core\Request\Criteria\CapabilityCriteria;
use ModelflowAi\Core\Request\Criteria\FeatureCriteria;
use ModelflowAi\Core\Request\Criteria\PrivacyCriteria;
use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Request\Message\ImageBase64Part;
use ModelflowAi\Core\Response\AIChatResponse;
use ModelflowAi\Core\Response\AIChatResponseMessage;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AIChatRequestTest extends TestCase
{
    use ProphecyTrait;

    public function testConstructor(): void
    {
        $message = new AIChatMessage(
            AIChatMessageRoleEnum::USER,
            new ImageBase64Part('iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg=='), // This is a 1x1 pixel white image in base64 format
        );
        $messages = new AIChatMessageCollection($message);
        $criteria = new AIRequestCriteriaCollection();
        $requestHandler = fn ($request) => null;

        $request = new AIChatRequest($messages, $criteria, $requestHandler);

        $this->assertTrue($request->matches(FeatureCriteria::IMAGE_TO_TEXT));
    }

    public function testExecute(): void
    {
        $message1 = new AIChatMessage(AIChatMessageRoleEnum::USER, 'Test content 1');
        $message2 = new AIChatMessage(AIChatMessageRoleEnum::USER, 'Test content 2');
        $criteriaCollection = new AIRequestCriteriaCollection();

        $requestHandler = fn ($request) => new AIChatResponse($request, new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Response content 1'));
        $request = new AIChatRequest(new AIChatMessageCollection($message1, $message2), $criteriaCollection, $requestHandler);

        $response = $request->execute();

        $this->assertInstanceOf(AIChatResponse::class, $response);
        $this->assertSame($request, $response->getRequest());
        $this->assertSame('Response content 1', $response->getMessage()->content);
    }

    public function testMatches(): void
    {
        $criteria1 = CapabilityCriteria::BASIC;
        $criteria2 = PrivacyCriteria::HIGH;
        $criteriaCollection = new AIRequestCriteriaCollection([$criteria1, $criteria2]);

        $requestHandler = fn () => null;
        $request = new AIChatRequest(new AIChatMessageCollection(), $criteriaCollection, $requestHandler);

        $this->assertTrue($request->matches(CapabilityCriteria::BASIC));
        $this->assertTrue($request->matches(PrivacyCriteria::LOW));
    }
}
