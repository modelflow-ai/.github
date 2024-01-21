<?php

namespace ModelflowAi\Integration\Symfony\DecisionTree;

use ModelflowAi\Core\DecisionTree\AIModelDecisionTree;
use ModelflowAi\Core\DecisionTree\AIModelDecisionTreeInterface;
use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\Core\Request\AIRequestInterface;

final readonly class AIModelDecisionTreeDecorator implements AIModelDecisionTreeInterface
{
    private AIModelDecisionTreeInterface $inner;

    public function __construct(
        \Traversable $rules,
    ) {
        $this->inner = new AIModelDecisionTree(\iterator_to_array($rules));
    }

    public function determineAdapter(AIRequestInterface $request): AIModelAdapterInterface
    {
        return $this->inner->determineAdapter($request);
    }
}
