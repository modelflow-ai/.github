<?php

namespace ModelflowAi\Core\Factory;

use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\Core\Request\Criteria\AiCriteriaInterface;

interface ChatAdapterFactoryInterface
{
    /**
     * @param array{
     *     model: string,
     *     image_to_text: bool,
     *     functions: bool,
     *     priority: int,
     * } $options
     */
    public function createChatAdapter(array $options): AIModelAdapterInterface;
}
