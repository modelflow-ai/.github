<?php

namespace ModelflowAi\Core\Factory;

use ModelflowAi\Core\Embeddings\EmbeddingAdapterInterface;
use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\Core\Request\Criteria\AiCriteriaInterface;

interface EmbeddingAdapterFactoryInterface
{
    /**
     * @param array{
     *     model: string,
     * } $options
     */
    public function createEmbeddingAdapter(array $options): EmbeddingAdapterInterface;
}
