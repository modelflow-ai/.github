<?php

namespace ModelflowAi\PromptTemplate;

use ModelflowAi\PromptTemplate\Chat\AIChatMessage;

readonly class ChatPromptTemplate
{
    public static function create(
        AIChatMessage ...$messages,
    ): self {
        return new self(...$messages);
    }

    /**
     * @var AIChatMessage[]
     */
    private array $messages;

    public function __construct(
        AIChatMessage ...$messages,
    ) {
        $this->messages = $messages;
    }

    /**
     * @param array<string, scalar> $inputValues
     *
     * @return AIChatMessage[]
     */
    public function format(array $inputValues = []): array
    {
        $messages = [];
        foreach ($this->messages as $message) {
            $template = new PromptTemplate($message->content);
            $messages[] = new AIChatMessage($message->role, $template->format($inputValues));
        }

        return $messages;
    }
}
