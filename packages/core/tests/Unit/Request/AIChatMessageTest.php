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

use ModelflowAi\PromptTemplate\Chat\AIChatMessage;
use ModelflowAi\PromptTemplate\Chat\AIChatMessageRoleEnum;
use PHPUnit\Framework\TestCase;

class AIChatMessageTest extends TestCase
{
    public function testConstructor(): void
    {
        $data = [
            'role' => AIChatMessageRoleEnum::USER,
            'content' => 'Test content',
        ];

        $message = $this->createInstance($data);

        $this->assertSame($data['role'], $message->role);
        $this->assertSame($data['content'], $message->content);
    }

    public function testToArray(): void
    {
        $data = [
            'role' => AIChatMessageRoleEnum::USER,
            'content' => 'Test content',
        ];

        $message = $this->createInstance($data);

        $this->assertSame([
            'role' => $data['role']->value,
            'content' => $data['content'],
        ], $message->toArray());
    }

    /**
     * @param array{
     *     role?: AIChatMessageRoleEnum,
     *     content?: string,
     * } $data
     */
    public function createInstance(array $data): AIChatMessage
    {
        $data['role'] ??= AIChatMessageRoleEnum::USER;
        $data['content'] ??= 'Test content';

        return new AIChatMessage($data['role'], $data['content']);
    }
}
