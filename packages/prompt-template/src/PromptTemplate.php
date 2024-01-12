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

readonly class PromptTemplate implements \Stringable
{
    public static function create(string $template): self
    {
        return new self($template);
    }

    public function __construct(
        private string $template,
    ) {
    }

    /**
     * @param array<string, string> $inputValues
     */
    public function format(array $inputValues = []): string
    {
        $prompt = $this->template;
        foreach ($inputValues as $key => $value) {
            $prompt = \str_replace("{{$key}}", $value, $prompt);
        }

        return $prompt;
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
