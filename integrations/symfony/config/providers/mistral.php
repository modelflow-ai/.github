<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use ModelflowAi\MistralAdapter\MistralAdapterFactory;
use ModelflowAi\Mistral\ClientInterface;
use ModelflowAi\Mistral\Factory;
use ModelflowAi\Mistral\Mistral;

/**
 * @internal
 */
return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('modelflow_ai.providers.mistral.client_factory', Factory::class)
        ->factory([Mistral::class, 'factory'])
        ->call('withApiKey', ['%modelflow_ai.providers.mistral.credentials.api_key%']);

    $container->services()
        ->set('modelflow_ai.providers.mistral.client', ClientInterface::class)
        ->factory([service('modelflow_ai.providers.mistral.client_factory'), 'make']);

    $container->services()
        ->set('modelflow_ai.providers.mistral.adapter_factory', MistralAdapterFactory::class)
        ->args([
            service('modelflow_ai.providers.mistral.client'),
        ]);
};
