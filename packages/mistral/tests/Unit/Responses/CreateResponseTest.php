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

namespace ModelflowAi\Mistral\Tests\Unit\Responses;

use ModelflowAi\Mistral\Responses\CreateResponse;
use ModelflowAi\Mistral\Responses\MetaInformation;
use ModelflowAi\Mistral\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;

final class CreateResponseTest extends TestCase
{
    public function testFrom(): void
    {
        $instance = CreateResponse::from(DataFixtures::CHAT_CREATE_RESPONSE, MetaInformation::from([]));

        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['id'], $instance->id);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['object'], $instance->object);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['created'], $instance->created);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['model'], $instance->model);
        $this->assertCount(\count(DataFixtures::CHAT_CREATE_RESPONSE['choices']), $instance->choices);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['choices'][0]['message']['role'], $instance->choices[0]->message->role);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['choices'][0]['message']['content'], $instance->choices[0]->message->content);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['choices'][0]['finish_reason'], $instance->choices[0]->finishReason);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['choices'][1]['message']['role'], $instance->choices[1]->message->role);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['choices'][1]['message']['content'], $instance->choices[1]->message->content);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['choices'][1]['finish_reason'], $instance->choices[1]->finishReason);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['usage']['prompt_tokens'], $instance->usage->promptTokens);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['usage']['completion_tokens'], $instance->usage->completionTokens);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['usage']['total_tokens'], $instance->usage->totalTokens);
        $this->assertInstanceOf(MetaInformation::class, $instance->meta);
    }
}
