<?php

namespace ModelflowAi\Core\ToolInfo;

use ModelflowAi\Core\Request\AIChatRequest;
use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use ModelflowAi\Core\Request\Message\ToolCallPart;
use ModelflowAi\Core\Response\AIChatToolCall;

interface ToolExecutorInterface
{
    public function execute(AIChatRequest $request, AIChatToolCall $toolCall): AIChatMessage;
}
