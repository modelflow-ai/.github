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

namespace ModelflowAi\mistral\tests\Unit\Responses\Chat;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\Mistral\Responses\Chat\CreateStreamedResponse;
use ModelflowAi\Mistral\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;

final class CreateStreamedResponseTest extends TestCase
{
    public function testFrom(): void
    {
        $attributes = DataFixtures::CHAT_CREATE_STREAMED_RESPONSES[0];

        $instance = CreateStreamedResponse::from(0, $attributes, MetaInformation::from([]));

        $this->assertSame($attributes['id'], $instance->id);
        $this->assertSame($attributes['object'], $instance->object);
        $this->assertSame($attributes['created'], $instance->created);
        $this->assertSame($attributes['model'], $instance->model);
        $this->assertCount(\count($attributes['choices']), $instance->choices);
        $this->assertSame($attributes['choices'][0]['delta']['role'], $instance->choices[0]->delta->role);
        $this->assertSame($attributes['choices'][0]['delta']['content'], $instance->choices[0]->delta->content);
        $this->assertSame($attributes['choices'][0]['finish_reason'], $instance->choices[0]->finishReason);
        $this->assertSame($attributes['usage']['prompt_tokens'], $instance->usage?->promptTokens);
        $this->assertSame($attributes['usage']['completion_tokens'], $instance->usage->completionTokens);
        $this->assertSame($attributes['usage']['total_tokens'], $instance->usage->totalTokens);
        $this->assertInstanceOf(MetaInformation::class, $instance->meta);
    }
}
