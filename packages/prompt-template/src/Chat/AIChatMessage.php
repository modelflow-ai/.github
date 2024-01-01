<?php

namespace ModelflowAi\PromptTemplate\Chat;

readonly class AIChatMessage
{
    public function __construct(
        public AIChatMessageRoleEnum $role,
        public string $content,
    ) {
    }
}
