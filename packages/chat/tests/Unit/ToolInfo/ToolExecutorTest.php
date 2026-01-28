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

namespace ModelflowAi\Chat\Tests\Unit\ToolInfo;

use ModelflowAi\Chat\Request\AIChatMessageCollection;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\Message\AIChatMessage;
use ModelflowAi\Chat\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Chat\Response\AIChatToolCall;
use ModelflowAi\Chat\ToolInfo\ToolExecutor;
use ModelflowAi\Chat\ToolInfo\ToolInfoBuilder;
use ModelflowAi\Chat\ToolInfo\ToolTypeEnum;
use ModelflowAi\DecisionTree\Criteria\CriteriaCollection;
use PHPUnit\Framework\TestCase;

class ToolExecutorTest extends TestCase
{
    public function testHandleTool(): void
    {
        $messages = new AIChatMessageCollection();
        $criteria = new CriteriaCollection();
        $requestHandler = static fn ($request) => null;

        $request = new AIChatRequest($messages, $criteria, [
            'test' => [$this, 'toolMethod'],
        ], [
            ToolInfoBuilder::buildToolInfo($this, 'toolMethod', 'test'),
        ], [], $requestHandler);

        $executor = new ToolExecutor();

        $result = $executor->execute(
            $request,
            new AIChatToolCall(ToolTypeEnum::FUNCTION, '123-123-123', 'test', ['test' => 'Test content']),
        );

        $this->assertInstanceOf(AIChatMessage::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::TOOL, $result->role);

        $array = $result->toArray();
        $this->assertSame([
            'role' => AIChatMessageRoleEnum::TOOL->value,
            'content' => 'Test content',
            'tool_call_id' => '123-123-123',
            'name' => 'test',
        ], $array);
    }

    public function testHandleToolWithException(): void
    {
        $messages = new AIChatMessageCollection();
        $criteria = new CriteriaCollection();
        $requestHandler = static fn ($request) => null;

        $request = new AIChatRequest($messages, $criteria, [
            'test' => [$this, 'toolMethodWithException'],
        ], [
            ToolInfoBuilder::buildToolInfo($this, 'toolMethodWithException', 'test'),
        ], [], $requestHandler);

        $executor = new ToolExecutor();

        $result = $executor->execute(
            $request,
            new AIChatToolCall(ToolTypeEnum::FUNCTION, '123-123-123', 'test', ['test' => 'Test content']),
        );

        $this->assertInstanceOf(AIChatMessage::class, $result);
        $this->assertSame(AIChatMessageRoleEnum::TOOL, $result->role);

        $array = $result->toArray();
        $this->assertSame([
            'role' => AIChatMessageRoleEnum::TOOL->value,
            'content' => 'Test exception',
            'tool_call_id' => '123-123-123',
            'name' => 'test',
        ], $array);
    }

    public function toolMethod(string $test): string
    {
        return $test;
    }

    public function toolMethodWithException(string $test): string
    {
        throw new \RuntimeException('Test exception');
    }
}
