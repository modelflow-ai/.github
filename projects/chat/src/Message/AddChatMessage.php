<?php

namespace App\Message;

use ModelflowAi\PromptTemplate\Chat\AIChatMessageRoleEnum;

final readonly class AddChatMessage
{
    public AIChatMessageRoleEnum $role;

    public function __construct(
        public string $uuid,
        public string $content,
    ) {
        $this->role = AIChatMessageRoleEnum::USER;
    }
}
