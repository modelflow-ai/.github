<?php

namespace ModelflowAi\Integration\Symfony\Config;

use ModelflowAi\Core\Request\Criteria\AiCriteriaInterface;

final readonly class AiCriteriaContainer implements AiCriteriaInterface
{
    public function __construct(
        private AiCriteriaInterface $inner,
    ) {
    }


    public function matches(AiCriteriaInterface $toMatch): bool
    {
        return $this->inner->matches($toMatch);
    }

    public function getValue(): int
    {
        return $this->inner->getValue();
    }

    public function getName(): string
    {
        return $this->inner->getName();
    }

    public function __toString(): string
    {
        return sprintf(
            '!php/const %s::%s',
            $this->inner::class,
            $this->inner->getName(),
        );
    }
}
