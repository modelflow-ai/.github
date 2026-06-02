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

namespace ModelflowAi\Anthropic\Tests\Unit\Resources;

use ModelflowAi\Anthropic\DataFixtures;
use ModelflowAi\Anthropic\Resources\Messages;
use ModelflowAi\Anthropic\Resources\MessagesInterface;
use ModelflowAi\Anthropic\Responses\Messages\CreateResponseContent;
use ModelflowAi\Anthropic\Responses\Messages\CreateResponseContentToolUse;
use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\ApiClient\Transport\Response\ObjectResponse;
use ModelflowAi\ApiClient\Transport\Testing\MockResponseMatcher;
use ModelflowAi\ApiClient\Transport\Testing\MockTransport;
use ModelflowAi\ApiClient\Transport\Testing\PartialPayload;
use ModelflowAi\ApiClient\Transport\Testing\StreamedResponse;
use PHPUnit\Framework\TestCase;

final class MessagesTest extends TestCase
{
    public function testCreate(): void
    {
        $mockResponseMatcher = new MockResponseMatcher();
        $instance = $this->createInstance($mockResponseMatcher);

        $mockResponseMatcher->addResponse(PartialPayload::create(
            'messages',
            DataFixtures::MESSAGES_CREATE_REQUEST,
        ), new ObjectResponse(DataFixtures::MESSAGES_CREATE_RESPONSE, MetaInformation::empty()));

        $response = $instance->create(DataFixtures::MESSAGES_CREATE_REQUEST_RAW);

        $responseData = DataFixtures::MESSAGES_CREATE_RESPONSE;
        $this->assertSame($responseData['id'], $response->id);
        $this->assertSame($responseData['type'], $response->type);
        $this->assertSame($responseData['role'], $response->role);
        $this->assertSame($responseData['model'], $response->model);
        $this->assertSame($responseData['stop_sequence'], $response->stopSequence);
        $this->assertSame($responseData['usage']['input_tokens'], $response->usage->promptTokens);
        $this->assertSame($responseData['usage']['output_tokens'], $response->usage->completionTokens);
        $this->assertSame($responseData['usage']['input_tokens'] + $responseData['usage']['output_tokens'], $response->usage->totalTokens);
        $this->assertSame($responseData['content'][0]['type'], $response->content[0]->type);
        $this->assertSame($responseData['content'][0]['text'], $response->content[0]->text);
        $this->assertSame($responseData['stop_reason'], $response->stopReason);
    }

    public function testCreateWithTools(): void
    {
        $mockResponseMatcher = new MockResponseMatcher();
        $instance = $this->createInstance($mockResponseMatcher);

        $mockResponseMatcher->addResponse(PartialPayload::create(
            'messages',
            DataFixtures::MESSAGES_CREATE_WITH_TOOLS_REQUEST,
        ), new ObjectResponse(DataFixtures::MESSAGES_CREATE_WITH_TOOLS_RESPONSE, MetaInformation::empty()));

        $response = $instance->create(DataFixtures::MESSAGES_CREATE_WITH_TOOLS_REQUEST_RAW);

        $responseData = DataFixtures::MESSAGES_CREATE_WITH_TOOLS_RESPONSE;
        $this->assertSame($responseData['id'], $response->id);
        $this->assertSame($responseData['type'], $response->type);
        $this->assertSame($responseData['role'], $response->role);
        $this->assertSame($responseData['model'], $response->model);
        $this->assertSame($responseData['stop_sequence'], $response->stopSequence);
        $this->assertSame($responseData['usage']['input_tokens'], $response->usage->promptTokens);
        $this->assertSame($responseData['usage']['output_tokens'], $response->usage->completionTokens);
        $this->assertSame($responseData['usage']['input_tokens'] + $responseData['usage']['output_tokens'], $response->usage->totalTokens);

        $content = $response->content[0];
        $this->assertInstanceOf(CreateResponseContent::class, $content);
        $toolUse = $content->toolUse;
        $this->assertInstanceOf(CreateResponseContentToolUse::class, $toolUse);

        $this->assertSame($responseData['content'][0]['type'], $content->type);
        $this->assertNull($content->text);
        $this->assertSame($responseData['content'][0]['id'], $toolUse->id);
        $this->assertSame($responseData['content'][0]['name'], $toolUse->name);
        $this->assertSame($responseData['content'][0]['input'], $toolUse->input);
        $this->assertSame($responseData['stop_reason'], $response->stopReason);
    }

    public function testCreateStreamed(): void
    {
        $mockResponseMatcher = new MockResponseMatcher();
        $instance = $this->createInstance($mockResponseMatcher);

        $responseChunks = [];
        foreach (DataFixtures::MESSAGES_CREATE_STREAMED_RESPONSES_RAW as $response) {
            $responseChunks[] = \implode(\PHP_EOL, $response);
        }

        $mockResponseMatcher->addResponse(PartialPayload::create(
            'messages',
            DataFixtures::MESSAGES_CREATE_STREAMED_REQUEST,
        ), new StreamedResponse($responseChunks, MetaInformation::empty()));

        $responses = $instance->createStreamed(DataFixtures::MESSAGES_CREATE_REQUEST_RAW);
        foreach ($responses as $index => $response) {
            $responseData = DataFixtures::MESSAGES_CREATE_STREAMED_RESPONSES[$index];
            $this->assertSame($responseData['id'], $response->id);
            $this->assertSame($responseData['type'], $response->type);
            $this->assertSame($responseData['role'], $response->role);
            $this->assertSame($responseData['model'], $response->model);
            $this->assertSame($responseData['stop_sequence'], $response->stopSequence);
            $this->assertSame($responseData['usage']['input_tokens'], $response->usage->promptTokens);
            $this->assertSame($responseData['usage']['output_tokens'], $response->usage->completionTokens);
            $this->assertSame($responseData['usage']['input_tokens'] + $responseData['usage']['output_tokens'], $response->usage->totalTokens);
            if (isset($responseData['content'])) {
                $this->assertSame($responseData['content']['index'], $response->content?->index);
                $this->assertSame($responseData['content']['type'], $response->content->type);
                $this->assertSame($responseData['content']['text'], $response->content->text);
            }
            $this->assertSame($responseData['stop_reason'], $response->stopReason);
        }
    }

    public function testCreateStreamedWithTools(): void
    {
        $mockResponseMatcher = new MockResponseMatcher();
        $instance = $this->createInstance($mockResponseMatcher);

        $responseChunks = [];
        foreach (DataFixtures::MESSAGES_CREATE_STREAMED_WITH_TOOLS_RESPONSES_RAW as $response) {
            $responseChunks[] = \implode(\PHP_EOL, $response);
        }

        $mockResponseMatcher->addResponse(PartialPayload::create(
            'messages',
            DataFixtures::MESSAGES_CREATE_STREAMED_REQUEST,
        ), new StreamedResponse($responseChunks, MetaInformation::empty()));

        $responses = \iterator_to_array($instance->createStreamed(DataFixtures::MESSAGES_CREATE_REQUEST_RAW));

        $toolUse = null;
        $inputDelta = null;
        $stopReason = null;
        foreach ($responses as $response) {
            if (null !== $response->content && 'tool_use' === $response->content->type) {
                $toolUse = $response->content;
            }
            if (null !== $response->content && 'input_json_delta' === $response->content->type) {
                $inputDelta = $response->content;
            }
            if (null !== $response->stopReason) {
                $stopReason = $response->stopReason;
            }
        }

        $this->assertNotNull($toolUse);
        $this->assertSame(0, $toolUse->index);
        $this->assertSame('toolu_01W7iPphiNtxfbEfsisKFGtd', $toolUse->id);
        $this->assertSame('get_weather', $toolUse->name);
        $this->assertSame([], $toolUse->input);

        $this->assertNotNull($inputDelta);
        $this->assertNotNull($inputDelta->partialJson);

        $this->assertSame('tool_use', $stopReason);
    }

    private function createInstance(MockResponseMatcher $responseMatcher): MessagesInterface
    {
        return new Messages(new MockTransport($responseMatcher));
    }
}
