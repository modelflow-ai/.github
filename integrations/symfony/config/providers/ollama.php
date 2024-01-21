<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use ModelflowAi\Ollama\ClientInterface;
use ModelflowAi\Ollama\Factory;
use ModelflowAi\Ollama\Ollama;
use ModelflowAi\OllamaAdapter\OllamaAdapterFactory;

/**
 * @internal
 */
return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('modelflow_ai.providers.ollama.client_factory', Factory::class)
        ->factory([Ollama::class, 'factory'])
        ->call('withBaseUrl', ['%modelflow_ai.providers.ollama.url%']);

    $container->services()
        ->set('modelflow_ai.providers.ollama.client', ClientInterface::class)
        ->factory([service('modelflow_ai.providers.ollama.client_factory'), 'make']);

    $container->services()
        ->set('modelflow_ai.providers.ollama.adapter_factory', OllamaAdapterFactory::class)
        ->args([
            service('modelflow_ai.providers.ollama.client'),
        ]);
};
