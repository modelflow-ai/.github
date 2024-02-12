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

namespace ModelflowAi\PromptTemplate;

use ModelflowAi\Core\Request\Message\AIChatMessage;
use ModelflowAi\Core\Request\Message\TextPart;

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
     * @param array<string, string> $inputValues
     *
     * @return AIChatMessage[]
     */
    public function format(array $inputValues = []): array
    {
        $messages = [];
        foreach ($this->messages as $message) {
            $parts = [];
            foreach ($message->parts as $part) {
                if ($part instanceof TextPart) {
                    $template = new PromptTemplate($part->text);
                    $parts[] = new TextPart($template->format($inputValues));

                    continue;
                }

                $parts[] = $part;
            }

            $messages[] = new AIChatMessage($message->role, $parts);
        }

        return $messages;
    }
}
