<?php

namespace ModelflowAi\Core\ToolInfo;

use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Request\Message\ToolCallPart;
use ModelflowAi\Core\Response\AIChatToolCall;

class ToolExecutor implements ToolExecutorInterface
{
    public function execute(AIChatRequest $request, AIChatToolCall $toolCall): AIChatMessage
    {
        try {
            $tool = $request->getTools()[$toolCall->name];
            $result = $tool[0]->{$tool[1]}(...$toolCall->arguments);
            if (!\is_string($result)) {
                $result = \json_encode($result, \JSON_THROW_ON_ERROR);
            }
        } catch (\Throwable $exception) {
            $result = $exception->getMessage();
        }

        return new AIChatMessage(AIChatMessageRoleEnum::TOOL, new ToolCallPart($toolCall->id, $toolCall->name, $result));
    }
}
