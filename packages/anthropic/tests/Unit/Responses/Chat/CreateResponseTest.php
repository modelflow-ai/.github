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

namespace ModelflowAi\Anthropic\Tests\Unit\Responses\Chat;

use ModelflowAi\Anthropic\DataFixtures;
use ModelflowAi\Anthropic\Responses\Messages\CreateResponse;
use ModelflowAi\Anthropic\Responses\Messages\CreateResponseContent;
use ModelflowAi\Anthropic\Responses\Messages\CreateResponseContentToolUse;
use ModelflowAi\ApiClient\Responses\MetaInformation;
use PHPUnit\Framework\TestCase;

final class CreateResponseTest extends TestCase
{
    public function testFrom(): void
    {
        $instance = CreateResponse::from(DataFixtures::MESSAGES_CREATE_RESPONSE, MetaInformation::empty());

        $responseData = DataFixtures::MESSAGES_CREATE_RESPONSE;
        $this->assertSame($responseData['id'], $instance->id);
        $this->assertSame($responseData['type'], $instance->type);
        $this->assertSame($responseData['role'], $instance->role);
        $this->assertSame($responseData['model'], $instance->model);
        $this->assertSame($responseData['stop_sequence'], $instance->stopSequence);
        $this->assertSame($responseData['usage']['input_tokens'], $instance->usage->promptTokens);
        $this->assertSame($responseData['usage']['output_tokens'], $instance->usage->completionTokens);
        $this->assertSame($responseData['usage']['input_tokens'] + $responseData['usage']['output_tokens'], $instance->usage->totalTokens);
        $this->assertSame($responseData['content'][0]['type'], $instance->content[0]->type);
        $this->assertSame($responseData['content'][0]['text'], $instance->content[0]->text);
        $this->assertSame($responseData['stop_reason'], $instance->stopReason);
        $this->assertInstanceOf(MetaInformation::class, $instance->meta);
    }

    public function testFromWithToolUse(): void
    {
        $instance = CreateResponse::from(DataFixtures::MESSAGES_CREATE_WITH_TOOLS_RESPONSE, MetaInformation::empty());

        $responseData = DataFixtures::MESSAGES_CREATE_WITH_TOOLS_RESPONSE;
        $this->assertSame($responseData['id'], $instance->id);
        $this->assertSame($responseData['type'], $instance->type);
        $this->assertSame($responseData['role'], $instance->role);
        $this->assertSame($responseData['model'], $instance->model);
        $this->assertSame($responseData['stop_sequence'], $instance->stopSequence);
        $this->assertSame($responseData['usage']['input_tokens'], $instance->usage->promptTokens);
        $this->assertSame($responseData['usage']['output_tokens'], $instance->usage->completionTokens);
        $this->assertSame($responseData['usage']['input_tokens'] + $responseData['usage']['output_tokens'], $instance->usage->totalTokens);

        $content = $instance->content[0];
        $this->assertInstanceOf(CreateResponseContent::class, $content);
        $toolUse = $content->toolUse;
        $this->assertInstanceOf(CreateResponseContentToolUse::class, $toolUse);

        $this->assertSame($responseData['content'][0]['type'], $content->type);
        $this->assertNull($content->text);
        $this->assertSame($responseData['content'][0]['id'], $toolUse->id);
        $this->assertSame($responseData['content'][0]['name'], $toolUse->name);
        $this->assertSame($responseData['content'][0]['input'], $toolUse->input);
        $this->assertSame($responseData['stop_reason'], $instance->stopReason);
        $this->assertInstanceOf(MetaInformation::class, $instance->meta);
    }

    public function testFromSkipsThinkingBlocks(): void
    {
        $instance = CreateResponse::from([
            'id' => 'msg_1',
            'type' => 'message',
            'role' => 'assistant',
            'model' => 'claude-haiku-5-5',
            'stop_sequence' => null,
            'usage' => ['input_tokens' => 1, 'output_tokens' => 2],
            'content' => [
                ['type' => 'thinking'],
                ['type' => 'redacted_thinking'],
                ['type' => 'text', 'text' => 'HALLO: WELT'],
            ],
            'stop_reason' => 'end_turn',
        ], MetaInformation::empty());

        $this->assertCount(1, $instance->content);
        $this->assertSame('text', $instance->content[0]->type);
        $this->assertSame('HALLO: WELT', $instance->content[0]->text);
    }
}
