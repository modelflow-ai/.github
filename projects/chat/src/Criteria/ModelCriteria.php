<?php

namespace App\Criteria;

use ModelflowAi\Core\Request\Criteria\AiCriteriaInterface;

enum ModelCriteria: string implements AiCriteriaInterface
{
    case LLAMA2 = 'llama2';
    case LLAVA = 'llava';
    case GPT4 = 'gpt4';
    case GPT3_5 = 'gpt3.5';
    case MISTRAL_TINY = 'mistral_tiny';
    case MISTRAL_SMALL = 'mistral_small';
    case MISTRAL_MEDIUM = 'mistral_medium';

    public function matches(AiCriteriaInterface $toMatch): bool
    {
        if (!$toMatch instanceof self) {
            return true;
        }

        return $this->getValue() === $toMatch->getValue();
    }

    public function getValue(): int|string
    {
        return $this->value;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
