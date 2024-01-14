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

namespace ModelflowAi\Ollama\Tests\Unit\Responses\Chat;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\Ollama\Responses\Chat\CreateResponse;
use ModelflowAi\Ollama\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;

final class CreateResponseTest extends TestCase
{
    public function testFrom(): void
    {
        $instance = CreateResponse::from(DataFixtures::CHAT_CREATE_RESPONSE, MetaInformation::from([]));

        $this->assertInstanceOf(CreateResponse::class, $instance);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['model'], $instance->model);
        $this->assertSame(
            (new \DateTimeImmutable(DataFixtures::CHAT_CREATE_RESPONSE['created_at']))->getTimestamp(),
            $instance->createdAt,
        );
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['message']['role'], $instance->message->role);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['message']['content'], $instance->message->content);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['done'], $instance->done);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['total_duration'], $instance->totalDuration);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['load_duration'], $instance->loadDuration);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['prompt_eval_duration'], $instance->promptEvalDuration);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['prompt_eval_count'], $instance->usage->promptTokens);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE['eval_count'], $instance->usage->totalTokens);
        $this->assertInstanceOf(MetaInformation::class, $instance->meta);
    }
}
