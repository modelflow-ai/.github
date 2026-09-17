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

$openaiClient = require_once __DIR__ . '/../bootstrap.php';

use ModelflowAi\DecisionTree\Criteria\CapabilityCriteria;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionTreeInterface;
use ModelflowAi\Image\Adapter\AIImageAdapterInterface;
use ModelflowAi\Image\AIImageRequestHandler;
use ModelflowAi\Image\Middleware\HandleMiddleware;
use ModelflowAi\Image\Request\AIImageRequest;
use ModelflowAi\OpenaiAdapter\Image\OpenAIImageGenerationAdapter;
use Symfony\Component\HttpClient\HttpClient;

$adapter = [];

$httpClient = HttpClient::create();

$dalle2 = new OpenAIImageGenerationAdapter($httpClient, $openaiClient, 'dall-e-2');
$adapter[] = new DecisionRule($dalle2, [CapabilityCriteria::BASIC]);

$dalle3 = new OpenAIImageGenerationAdapter($httpClient, $openaiClient, 'dall-e-3');
$adapter[] = new DecisionRule($dalle3, [CapabilityCriteria::SMART]);

/** @var DecisionTreeInterface<AIImageRequest, AIImageAdapterInterface> $decisionTree */
$decisionTree = new DecisionTree($adapter);

return new AIImageRequestHandler(new HandleMiddleware($decisionTree));
