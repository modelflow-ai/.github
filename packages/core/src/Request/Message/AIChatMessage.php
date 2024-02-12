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

namespace ModelflowAi\Core\Request\Message;

use Webmozart\Assert\Assert;

readonly class AIChatMessage
{
    /**
     * @var MessagePart[]
     */
    public array $parts;

    /**
     * @param MessagePart[]|MessagePart|string $content
     */
    public function __construct(
        public AIChatMessageRoleEnum $role,
        array|MessagePart|string $content,
    ) {
        if ($content instanceof MessagePart) {
            $this->parts = [$content];
        } elseif (\is_string($content)) {
            $this->parts = [TextPart::create($content)];
        } else {
            Assert::allIsInstanceOf($content, MessagePart::class);
            $this->parts = $content;
        }
    }

    /**
     * @return array{
     *     role: "assistant"|"system"|"user",
     *     content: string,
     *     images?: string[],
     * }
     */
    public function toArray(): array
    {
        $message = [
            'role' => $this->role->value,
            'content' => '',
        ];

        /** @var MessagePart $part */
        foreach ($this->parts as $part) {
            $message = $part->enhanceMessage($message);
        }

        return $message;
    }
}
