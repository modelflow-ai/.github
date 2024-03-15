<?php

namespace App\Message;

use ModelflowAi\Core\Request\Message\AIChatMessageRoleEnum;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class AddChatMessage
{
    public AIChatMessageRoleEnum $role;

    public function __construct(
        public string $uuid,
        public string $content,
        public ?UploadedFile $file = null,
        public bool $enableTools = false,
    ) {
        $this->role = AIChatMessageRoleEnum::USER;
    }
}
