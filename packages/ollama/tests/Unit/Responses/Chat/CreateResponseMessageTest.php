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

use ModelflowAi\Ollama\Responses\Chat\CreateResponseMessage;
use ModelflowAi\Ollama\Tests\DataFixtures;
use PHPUnit\Framework\TestCase;

final class CreateResponseMessageTest extends TestCase
{
    public function testFrom(): void
    {
        $messageData = DataFixtures::CHAT_CREATE_RESPONSE['message'];

        $message = CreateResponseMessage::from($messageData);

        $this->assertInstanceOf(CreateResponseMessage::class, $message);
        $this->assertSame($messageData['role'], $message->role);
        $this->assertSame($messageData['content'], $message->content);
    }
}
