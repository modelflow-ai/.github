<?php

namespace ModelflowAi\Core\Factory;

use ModelflowAi\Core\Model\AIModelAdapterInterface;

interface TextAdapterFactoryInterface
{
    /**
     * @param array{
     *     model: string,
     *     image_to_text: bool,
     *     functions: bool,
     * } $options
     */
    public function createTextAdapter(array $options): AIModelAdapterInterface;
}
