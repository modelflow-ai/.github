<?php

namespace ModelflowAi\Integration\Symfony;

use ModelflowAi\Core\DecisionTree\DecisionRule;
use ModelflowAi\Core\Embeddings\EmbeddingAdapterInterface;
use ModelflowAi\Core\Model\AIModelAdapterInterface;
use ModelflowAi\Core\Request\Criteria\AiCriteriaInterface;
use ModelflowAi\Core\Request\Criteria\CapabilityRequirement;
use ModelflowAi\Core\Request\Criteria\PrivacyRequirement;
use ModelflowAi\Embeddings\Adapter\Cache\CacheEmbeddingAdapter;
use ModelflowAi\Embeddings\Formatter\EmbeddingFormatter;
use ModelflowAi\Embeddings\Generator\EmbeddingGenerator;
use ModelflowAi\Embeddings\Splitter\EmbeddingSplitter;
use ModelflowAi\Integration\Symfony\Config\AiCriteriaContainer;
use ModelflowAi\MistralAdapter\MistralAdapterFactory;
use ModelflowAi\OllamaAdapter\OllamaAdapterFactory;
use ModelflowAi\OpenaiAdapter\OpenaiAdapterFactory;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class ModelflowAiBundle extends AbstractBundle
{
    protected string $extensionAlias = 'modelflow_ai';

    const DEFAULT_ADAPTER_KEY_ORDER = [
        'enabled',
        'provider',
        'model',
        'functions',
        'image_to_text',
        'criteria',
        'priority',
    ];

    const DEFAULT_VALUES = [
        'gpt4' => [
            'provider' => 'openai',
            'model' => 'gpt4',
            'functions' => true,
            'image_to_text' => false,
            'criteria' => [
                CapabilityRequirement::SMART,
            ],
        ],
        'gpt3.5' => [
            'provider' => 'openai',
            'model' => 'gpt3.5',
            'functions' => false,
            'image_to_text' => false,
            'criteria' => [
                CapabilityRequirement::INTERMEDIATE,
            ],
        ],
        'mistral_tiny' => [
            'provider' => 'mistral',
            'model' => 'mistral-tiny',
            'functions' => false,
            'image_to_text' => false,
            'criteria' => [
                CapabilityRequirement::BASIC,
            ],
        ],
        'mistral_small' => [
            'provider' => 'mistral',
            'model' => 'mistral-small',
            'functions' => false,
            'image_to_text' => false,
            'criteria' => [
                CapabilityRequirement::INTERMEDIATE,
            ],
        ],
        'mistral_medium' => [
            'provider' => 'mistral',
            'model' => 'mistral-medium',
            'functions' => false,
            'image_to_text' => false,
            'criteria' => [
                CapabilityRequirement::ADVANCED,
            ],
        ],
        'llama2' => [
            'provider' => 'ollama',
            'model' => 'llama2',
            'functions' => false,
            'image_to_text' => false,
            'criteria' => [
                CapabilityRequirement::BASIC,
            ],
        ],
        'nexusraven' => [
            'provider' => 'ollama',
            'model' => 'nexusraven',
            'functions' => true,
            'image_to_text' => false,
            'criteria' => [
                CapabilityRequirement::BASIC,
            ],
        ],
        'llava' => [
            'provider' => 'ollama',
            'model' => 'llava',
            'functions' => false,
            'image_to_text' => true,
            'criteria' => [
                CapabilityRequirement::BASIC,
            ],
        ],
    ];

    private static function getCriteria(AiCriteriaInterface $criteria, bool $isReferenceDumping, bool $isDebugConfiguration): AiCriteriaInterface|string
    {
        if ($isReferenceDumping) {
            return new AiCriteriaContainer($criteria);
        }

        return $criteria;
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $arguments = $argv ?? $_SERVER['argv'] ?? null;

        $isReferenceDumping = false;
        $isDebugConfiguration = false;
        $container = $this->container ?? null;
        if ($container && $arguments) {
            $application = new Application($container->get('kernel'));
            $command = $application->find($arguments[1] ?? null);
            $isReferenceDumping = $command->getName() === 'config:dump-reference';
            $isDebugConfiguration = $command->getName() === 'debug:config';
        }

        $adapters = [];

        // @phpstan-ignore-next-line
        $definition->rootNode()
            ->children()
                ->arrayNode('providers')
                    ->children()
                        ->arrayNode('openai')
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->arrayNode('credentials')
                                    ->isRequired()
                                    ->children()
                                        ->scalarNode('api_key')->isRequired()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('criteria')
                                    ->defaultValue([
                                        self::getCriteria(PrivacyRequirement::LOW, $isReferenceDumping, $isDebugConfiguration),
                                    ])
                                    ->beforeNormalization()
                                        ->ifArray()
                                        ->then(static function ($value) use ($isReferenceDumping, $isDebugConfiguration): array {
                                            $result = [];
                                            foreach ($value as $item) {
                                                if ($item instanceof AiCriteriaInterface) {
                                                    $result[] = self::getCriteria($item, $isReferenceDumping, $isDebugConfiguration);
                                                } else {
                                                    $result[] = $item;
                                                }
                                            }

                                            return $result;
                                        })
                                    ->end()
                                    ->variablePrototype()
                                        ->validate()
                                            ->ifTrue(static function ($value): bool {
                                                return !$value instanceof AiCriteriaInterface;
                                            })
                                            ->thenInvalid('The value has to be an instance of AiCriteriaInterface')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('mistral')
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->arrayNode('credentials')
                                    ->isRequired()
                                    ->children()
                                        ->scalarNode('api_key')->isRequired()->end()
                                    ->end()
                                ->end()
                                ->arrayNode('criteria')
                                    ->defaultValue([
                                        self::getCriteria(PrivacyRequirement::MEDIUM, $isReferenceDumping, $isDebugConfiguration),
                                    ])
                                    ->beforeNormalization()
                                        ->ifArray()
                                        ->then(static function ($value) use ($isReferenceDumping, $isDebugConfiguration): array {
                                            $result = [];
                                            foreach ($value as $item) {
                                                if ($item instanceof AiCriteriaInterface) {
                                                    $result[] = self::getCriteria($item, $isReferenceDumping, $isDebugConfiguration);
                                                } else {
                                                    $result[] = $item;
                                                }
                                            }

                                            return $result;
                                        })
                                    ->end()
                                    ->variablePrototype()
                                        ->validate()
                                            ->ifTrue(static function ($value): bool {
                                                return !$value instanceof AiCriteriaInterface;
                                            })
                                            ->thenInvalid('The value has to be an instance of AiCriteriaInterface')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('ollama')
                            ->children()
                                ->booleanNode('enabled')->defaultFalse()->end()
                                ->scalarNode('url')
                                    ->defaultValue('http://localhost:11434/api/')
                                    ->validate()
                                        ->ifTrue(static function ($value): bool {
                                            return !\filter_var($value, FILTER_VALIDATE_URL);
                                        })
                                        ->thenInvalid('The value has to be a valid URL')
                                    ->end()
                                    ->beforeNormalization()
                                        ->ifString()
                                        ->then(static function ($value): string {
                                            return \rtrim($value, '/') . '/';
                                        })
                                    ->end()
                                ->end()
                                ->arrayNode('criteria')
                                    ->defaultValue([
                                        self::getCriteria(PrivacyRequirement::HIGH, $isReferenceDumping, $isDebugConfiguration),
                                    ])
                                    ->beforeNormalization()
                                        ->ifArray()
                                        ->then(static function ($value) use ($isReferenceDumping, $isDebugConfiguration): array {
                                            $result = [];
                                            foreach ($value as $item) {
                                                if ($item instanceof AiCriteriaInterface) {
                                                    $result[] = self::getCriteria($item, $isReferenceDumping, $isDebugConfiguration);
                                                } else {
                                                    $result[] = $item;
                                                }
                                            }

                                            return $result;
                                        })
                                    ->end()
                                    ->variablePrototype()
                                        ->validate()
                                            ->ifTrue(static function ($value): bool {
                                                return !$value instanceof AiCriteriaInterface;
                                            })
                                            ->thenInvalid('The value has to be an instance of AiCriteriaInterface')
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('adapters')
                    ->info('You can configure your own adapter here or use a preconfigured one (see examples) and enable it.')
                    ->example(self::DEFAULT_VALUES)
                    ->beforeNormalization()
                        ->ifArray()
                        ->then(static function ($value) use (&$adapters): array {
                            foreach ($value as $key => $item) {
                                $value[$key]['key'] = $key;
                                $adapters[$key] = $item;
                            }

                            return $value;
                        })
                    ->end()
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifArray()
                            ->then(static function ($value): array {
                                $key = $value['key'];
                                unset($value['key']);

                                $enabled = $value['enabled'] ?? false;
                                unset($value['enabled']);

                                if (count($value) !== 0) {
                                    $enabled = true;
                                }

                                $value = \array_merge(self::DEFAULT_VALUES[$key], $value);
                                $value['enabled'] = $enabled;

                                uksort($value, function($key1, $key2) {
                                    return ((array_search($key1, self::DEFAULT_ADAPTER_KEY_ORDER) > array_search($key2, self::DEFAULT_ADAPTER_KEY_ORDER)) ? 1 : -1);
                                });

                                return $value;
                            })
                        ->end()
                        ->children()
                            ->booleanNode('enabled')->defaultFalse()->end()
                            ->scalarNode('provider')->isRequired()->end()
                            ->scalarNode('model')->isRequired()->end()
                            ->integerNode('priority')->defaultValue(0)->end()
                            ->booleanNode('functions')->defaultFalse()->end()
                            ->booleanNode('image_to_text')->defaultFalse()->end()
                            ->arrayNode('criteria')
                                ->beforeNormalization()
                                    ->ifArray()
                                    ->then(static function ($value) use ($isReferenceDumping, $isDebugConfiguration): array {
                                        $result = [];
                                        foreach ($value as $item) {
                                            if ($item instanceof AiCriteriaInterface) {
                                                $result[] = self::getCriteria($item, $isReferenceDumping, $isDebugConfiguration);
                                            } else {
                                                $result[] = $item;
                                            }
                                        }

                                        return $result;
                                    })
                                ->end()
                                ->variablePrototype()
                                    ->validate()
                                        ->ifTrue(static function ($value): bool {
                                            return !$value instanceof AiCriteriaInterface;
                                        })
                                        ->thenInvalid('The value has to be an instance of AiCriteriaInterface')
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('embeddings')
                    ->children()
                        ->arrayNode('generators')
                            ->arrayPrototype()
                                ->children()
                                    ->booleanNode('enabled')->defaultFalse()->end()
                                    ->scalarNode('provider')->end()
                                    ->scalarNode('model')->end()
                                    ->arrayNode('splitter')
                                        ->addDefaultsIfNotSet()
                                        ->children()
                                            ->integerNode('max_length')->defaultValue(1000)->end()
                                            ->scalarNode('separator')->defaultValue(' ')->end()
                                        ->end()
                                    ->end()
                                    ->arrayNode('cache')
                                        ->children()
                                            ->booleanNode('enabled')->defaultFalse()->end()
                                            ->scalarNode('cache_pool')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('chat')
                    ->children()
                        ->arrayNode('adapters')
                            ->scalarPrototype()
                                ->validate()
                                    ->ifTrue(static function ($value) use (&$adapters): bool {
                                        return !\in_array($value, \array_keys($adapters), true);
                                    })
                                    ->thenInvalid('The value has to be a valid adapter key')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('text')
                    ->children()
                        ->arrayNode('adapters')
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (\is_array($value)) {
                $result = \array_merge($result, $this->flattenArray($value, $prefix . $key . '.'));
            } else {
                $result[$prefix . $key] = $value;
            }
        }

        return $result;
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $providerConfig = $this->flattenArray($config['providers']);
        foreach ($providerConfig as $key => $value) {
            $container->parameters()
                ->set('modelflow_ai.providers.' . $key, $value);
        }

        $container->import(\dirname(__DIR__) . '/config/request_handler.php');
        $container->import(\dirname(__DIR__) . '/config/commands.php');

        $adapters = array_filter($config['adapters'], fn(array $adapter) => $adapter['enabled'] ?? false);
        $providers = array_filter($config['providers'], fn(array $provider) => $provider['enabled'] ?? false);

        if ($providers['openai']['enabled'] ?? false) {
            if (!class_exists(OpenaiAdapterFactory::class)) {
                throw new \Exception('OpenAi adapter is enabled but the OpenAi adapter library is not installed. Please install it with composer require modelflow-ai/openai-adapter');
            }

            $container->import(\dirname(__DIR__) . '/config/providers/openai.php');
        }

        if ($providers['mistral']['enabled'] ?? false) {
            if (!class_exists(MistralAdapterFactory::class)) {
                throw new \Exception('Mistral adapter is enabled but the Mistral adapter library is not installed. Please install it with composer require modelflow-ai/mistral-adapter');
            }

            $container->import(\dirname(__DIR__) . '/config/providers/mistral.php');
        }

        if ($providers['ollama']['enabled'] ?? false) {
            if (!class_exists(OllamaAdapterFactory::class)) {
                throw new \Exception('Ollama adapter is enabled but the Ollama adapter library is not installed. Please install it with composer require modelflow-ai/ollama-adapter');
            }

            $container->import(\dirname(__DIR__) . '/config/providers/ollama.php');
        }

        foreach ($config['chat']['adapters'] as $key){
            $adapter = $adapters[$key] ?? null;
            if (!$adapter) {
                throw new \Exception('Chat adapter ' . $key . ' is enabled but not configured.');
            }

            $provider = $providers[$adapter['provider']] ?? null;
            if(!$provider) {
                throw new \Exception('Chat adapter ' . $key . ' is enabled but the provider ' . $adapter['provider'] . ' is not enabled.');
            }

            $container->services()
                ->set('modelflow_ai.chat_adapter.' . $key . '.adapter', AIModelAdapterInterface::class)
                ->factory([service('modelflow_ai.providers.' . $adapter['provider'] . '.adapter_factory'), 'createChatAdapter'])
                ->args([
                    $adapter,
                ]);

            $container->services()
                ->set('modelflow_ai.chat_adapter.' . $key . '.rule', DecisionRule::class)
                ->args([
                    service('modelflow_ai.chat_adapter.' . $key . '.adapter'),
                   \array_merge($provider['criteria'], $adapter['criteria']),
                ])
                ->tag('modelflow_ai.decision_tree.rule');
        }

        foreach ($config['text']['adapters'] as $key) {
            $adapter = $adapters[$key] ?? null;
            if (!$adapter) {
                throw new \Exception('Text adapter ' . $key . ' is enabled but not configured.');
            }

            $provider = $providers[$adapter['provider']] ?? null;
            if(!$provider) {
                throw new \Exception('Text adapter ' . $key . ' is enabled but the provider ' . $adapter['provider'] . ' is not enabled.');
            }

            $container->services()
                ->set('modelflow_ai.text_adapter.' . $key . '.adapter', AIModelAdapterInterface::class)
                ->factory([service('modelflow_ai.providers.' . $adapter['provider'] . '.adapter_factory'), 'createTextAdapter'])
                ->args([
                    $adapter
                ]);

            $container->services()
                ->set('modelflow_ai.text_adapter.' . $key . '.rule', DecisionRule::class)
                ->args([
                    service('modelflow_ai.chat_adapter.' . $key . '.adapter'),
                   \array_merge($provider['criteria'], $adapter['criteria']),
                ])
                ->tag('modelflow_ai.decision_tree.rule');
        }

        foreach ($config['embeddings']['generators'] as $key => $embedding) {
            $adapterId = $key . '.adapter';
            $container->services()
                ->set($adapterId, EmbeddingAdapterInterface::class)
                ->factory([service('modelflow_ai.providers.' . $embedding['provider'] . '.adapter_factory'), 'createEmbeddingGenerator'])
                ->args([
                    $embedding,
                ]);

            if ($embedding['cache']['enabled'] ?? false) {
                $container->services()
                    ->set($adapterId . '.cache', CacheEmbeddingAdapter::class)
                    ->args([
                        service($adapterId),
                        service($embedding['cache']['cache_pool']),
                    ]);

                $adapterId = $adapterId . '.cache';
            }

            $container->services()
                ->set($key . '.splitter', EmbeddingSplitter::class)
                ->args([
                    $embedding['splitter']['max_length'],
                    $embedding['splitter']['separator'],
                ]);

            $container->services()
                ->set($key . '.formatter', EmbeddingFormatter::class);

            $container->services()
                ->set($key . '.generator', EmbeddingGenerator::class)
                ->args([
                    service($key . '.splitter'),
                    service($key . '.formatter'),
                    service($adapterId),
                ]);
        }
    }
}
