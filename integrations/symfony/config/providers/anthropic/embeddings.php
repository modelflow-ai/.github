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

use ModelflowAi\AnthropicAdapter\Embeddings\AnthropicEmbeddingsAdapterFactory;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('modelflow_ai.providers.anthropic.embedding_adapter_factory', AnthropicEmbeddingsAdapterFactory::class)
        ->args([service('modelflow_ai.providers.anthropic.client')]);
};