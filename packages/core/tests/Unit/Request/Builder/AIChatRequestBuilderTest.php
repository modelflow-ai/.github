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

namespace ModelflowAi\Core\Tests\Unit\Request\Builder;

use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\Builder\AIChatRequestBuilder;
use ModelflowAi\PromptTemplate\Chat\AIChatMessage;
use ModelflowAi\PromptTemplate\Chat\AIChatMessageRoleEnum;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AIChatRequestBuilderTest extends TestCase
{
    use ProphecyTrait;

    public function testAddMessage(): void
    {
        $builder = new AIChatRequestBuilder(fn () => null);
        $message = new AIChatMessage(AIChatMessageRoleEnum::USER, 'test message');

        $builder->addMessage($message);

        $this->assertCount(1, $builder->build()->getMessages());
    }

    public function testAddMessages(): void
    {
        $builder = new AIChatRequestBuilder(fn () => null);
        $messages = [
            new AIChatMessage(AIChatMessageRoleEnum::USER, 'test message'),
            new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'test message'),
        ];

        $builder->addMessages($messages);

        $this->assertCount(2, $builder->build()->getMessages());
    }

    public function testBuild(): void
    {
        $builder = new AIChatRequestBuilder(fn () => null);
        $message = new AIChatMessage(AIChatMessageRoleEnum::USER, 'test message');

        $builder->addMessage($message);

        $this->assertInstanceOf(
            AIChatRequest::class,
            $builder->build(),
        );
    }
}
