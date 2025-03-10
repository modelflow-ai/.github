<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $providers = [];
    $adapters = [];

    if (isset($_ENV['OLLAMA_URL']) && '' !== $_ENV['OLLAMA_URL']) {
        $providers['ollama'] = [
            'enabled' => true,
            'url' => '%env(OLLAMA_URL)%',
        ];

        $adapters = array_merge($adapters, [
            'llama3_2' => [
                'enabled' => true,
            ],
            'llama3' => [
                'enabled' => true,
            ],
            'llama2' => [
                'enabled' => true,
            ],
            'nexusraven' => [
                'enabled' => true,
            ],
            'llava' => [
                'enabled' => true,
            ],
        ]);
    }

    if (isset($_ENV['OPENAI_API_KEY']) && '' !== $_ENV['OPENAI_API_KEY']) {
        $providers['openai'] = [
            'enabled' => true,
            'credentials' => [
                'api_key' => '%env(OPENAI_API_KEY)%',
            ],
        ];

        $adapters = array_merge($adapters, [
            'gpt4o' => [
                'enabled' => true,
            ],
            'gpt4o_mini' => [
                'enabled' => true,
            ],
            'gpt4' => [
                'enabled' => true,
            ],
            'gpt3.5' => [
                'enabled' => true,
            ],
            'dall_e_2' => [
                'enabled' => true,
            ],
            'dall_e_3' => [
                'enabled' => true,
            ],
        ]);
    }

    if (isset($_ENV['MISTRAL_API_KEY']) && '' !== $_ENV['MISTRAL_API_KEY']) {
        $providers['mistral'] = [
            'enabled' => true,
            'credentials' => [
                'api_key' => '%env(MISTRAL_API_KEY)%',
            ],
        ];

        $adapters = array_merge($adapters, [
            'mistral_tiny' => [
                'enabled' => true,
            ],
            'mistral_small' => [
                'enabled' => true,
            ],
            'mistral_medium' => [
                'enabled' => true,
            ],
            'mistral_nemo' => [
                'enabled' => true,
            ],
            'mistral_large' => [
                'enabled' => true,
            ],
            'pixtral_large' => [
                'enabled' => true,
            ],
        ]);
    }

    if (isset($_ENV['ANTHROPIC_API_KEY']) && '' !== $_ENV['ANTHROPIC_API_KEY']) {
        $providers['anthropic'] = [
            'enabled' => true,
            'credentials' => [
                'api_key' => '%env(ANTHROPIC_API_KEY)%',
            ],
        ];

        $adapters = array_merge($adapters, [
            'claude_3_opus' => [
                'enabled' => true,
            ],
            'claude_3_5_sonnet' => [
                'enabled' => true,
            ],
            'claude_3_sonnet' => [
                'enabled' => true,
            ],
            'claude_3_5_haiku' => [
                'enabled' => true,
            ],
            'claude_3_haiku' => [
                'enabled' => true,
            ],
        ]);
    }

    if (isset($_ENV['FIREWORKSAI_API_KEY']) && '' !== $_ENV['FIREWORKSAI_API_KEY']) {
        $providers['fireworksai'] = [
            'enabled' => true,
            'credentials' => [
                'api_key' => '%env(FIREWORKSAI_API_KEY)%',
            ],
        ];

        $adapters = array_merge($adapters, [
            'fireworksai_llama3_1_405b' => [
                'enabled' => true,
            ],
            'fireworksai_llama3_1_70b' => [
                'enabled' => true,
            ],
            'fireworksai_llama3_1_8b' => [
                'enabled' => true,
            ],
            'fireworksai_llama3_70b' => [
                'enabled' => true,
            ],
            'fireworksai_mixtral' => [
                'enabled' => true,
            ],
            'fireworksai_firefunction_v2' => [
                'enabled' => true,
            ],
            'fireworksai_llava_13b' => [
                'enabled' => true,
            ],
            'stable_diffusion_xl_fireworks' => [
                'enabled' => true,
            ],
        ]);
    }

    if (isset($_ENV['GOOGLE_GEMINI_API_KEY']) && '' !== $_ENV['GOOGLE_GEMINI_API_KEY']) {
        $providers['google_gemini'] = [
            'enabled' => true,
            'credentials' => [
                'api_key' => '%env(GOOGLE_GEMINI_API_KEY)%',
            ],
        ];

        $adapters = array_merge($adapters, [
            'gemini_2_0_flash' => [
                'enabled' => true,
            ],
        ]);
    }

    $container->extension('modelflow_ai', [
        'providers' => $providers,
        'adapters' => $adapters,
    ]);
};
