<?php

namespace ModelflowAi\Core\Request;

class AIChatMessage
{
    public function __construct(
        public AIChatMessageRoleEnum $role,
        public string $content,
    ) {
    }

    public function toArray(): array
    {
        return [
            'role' => $this->role->value,
            'content' => $this->content,
        ];
    }
}
