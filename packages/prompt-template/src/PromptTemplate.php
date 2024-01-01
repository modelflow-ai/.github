<?php

namespace ModelflowAi\PromptTemplate;

readonly class PromptTemplate
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
     * @param array<string, scalar> $inputValues
     */
    public function format(array $inputValues = []): string
    {
        $prompt = $this->template;
        foreach ($inputValues as $key => $value) {
            $prompt = str_replace("{{$key}}", $value, $prompt);
        }

        return $prompt;
    }

    public function __toString(): string
    {
        return $this->format();
    }
}
