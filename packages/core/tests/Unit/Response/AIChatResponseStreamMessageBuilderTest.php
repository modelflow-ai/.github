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

namespace ModelflowAi\Core\Tests\Unit\Response;

use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Response\AIChatResponseMessage;
use ModelflowAi\Core\Response\AIChatResponseStreamMessageBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AIChatResponseStreamMessageBuilderTest extends TestCase
{
    use ProphecyTrait;

    public function testGetMessage(): void
    {
        $builder = new AIChatResponseStreamMessageBuilder([
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Lorem'),
            new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Ipsum'),
        ]);

        $this->assertSame('LoremIpsum', $builder->getMessage()->content);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $builder->getMessage()->role);
    }

    public function testAdd(): void
    {
        $builder = new AIChatResponseStreamMessageBuilder();
        $builder->add(new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Lorem'));
        $builder->add(new AIChatResponseMessage(AIChatMessageRoleEnum::ASSISTANT, 'Ipsum'));

        $this->assertSame('LoremIpsum', $builder->getMessage()->content);
        $this->assertSame(AIChatMessageRoleEnum::ASSISTANT, $builder->getMessage()->role);
    }
}
