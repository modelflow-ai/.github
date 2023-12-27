<?php

namespace ModelflowAi\Core\Tests\Unit\Request;

use ModelflowAi\Core\Request\AIChatMessage;
use ModelflowAi\Core\Request\AIChatMessageRoleEnum;
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
        $data['role'] = $data['role'] ?? AIChatMessageRoleEnum::USER;
        $data['content'] = $data['content'] ?? 'Test content';

        return new AIChatMessage($data['role'], $data['content']);
    }
}
