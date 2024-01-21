<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use ModelflowAi\Core\AIRequestHandler;
use ModelflowAi\Core\DecisionTree\AIModelDecisionTree;
use ModelflowAi\Core\DecisionTree\AIModelDecisionTreeInterface;
use ModelflowAi\Core\DecisionTree\DecisionRule;
use ModelflowAi\Integration\Symfony\Command\ChatCommand;

/**
 * @internal
 */
return static function (ContainerConfigurator $container) {
    $container->services()
        ->set('modelflow_ai.command.chat', ChatCommand::class)
        ->args([
            service('modelflow_ai.request_handler'),
        ])
        ->tag('console.command', ['command' => 'modelflow-ai:chat']);
};
