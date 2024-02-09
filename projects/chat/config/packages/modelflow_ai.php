<?php

use App\Criteria\ModelCriteria;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $providers = [];
    $adapters = [];

    if (isset($_ENV['OLLAMA_URL'])) {
        $providers['ollama'] = [
            'enabled' => true,
            'url' => '%env(OLLAMA_URL)%',
        ];

        $adapters = array_merge($adapters, [
            'llama2' => [
                'enabled' => true,
                'criteria' => [
                    ModelCriteria::LLAMA2,
                ],
            ],
            'llava' => [
                'enabled' => true,
                'criteria' => [
                    ModelCriteria::LLAVA,
                ],
            ],
        ]);
    }

    if (isset($_ENV['OPENAI_API_KEY'])) {
        $providers['openai'] = [
            'enabled' => true,
            'credentials' => [
                'api_key' => '%env(OPENAI_API_KEY)%',
            ],
        ];

        $adapters = array_merge($adapters, [
            'gpt4' => [
                'enabled' => true,
                'criteria' => [
                    ModelCriteria::GPT4,
                ],
            ],
            'gpt3.5' => [
                'enabled' => true,
                'criteria' => [
                    ModelCriteria::GPT3_5,
                ],
            ],
        ]);
    }

    if (isset($_ENV['MISTRAL_API_KEY'])) {
        $providers['mistral'] = [
            'enabled' => true,
            'credentials' => [
                'api_key' => '%env(MISTRAL_API_KEY)%',
            ],
        ];

        $adapters = array_merge($adapters, [
            'mistral_tiny' => [
                'enabled' => true,
                'criteria' => [
                    ModelCriteria::MISTRAL_TINY,
                ],
            ],
            'mistral_small' => [
                'enabled' => true,
                'criteria' => [
                    ModelCriteria::MISTRAL_SMALL,
                ],
            ],
            'mistral_medium' => [
                'enabled' => true,
                'criteria' => [
                    ModelCriteria::MISTRAL_MEDIUM,
                ],
            ],
        ]);
    }

    $container->extension('modelflow_ai', [
        'providers' => $providers,
        'adapters' => $adapters,
        'chat' => [
            'adapters' => array_keys($adapters),
        ],
    ]);

    $container->extension('twig', [
        'globals' => [
            'MODELS' => array_keys($adapters),
        ],
    ]);
};
