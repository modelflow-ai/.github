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

use ModelflowAi\DecisionTree\Criteria\CapabilityCriteria;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionTreeInterface;
use ModelflowAi\FireworksAiAdapter\Image\FireworksAiImageGenerationAdapter;
use ModelflowAi\Image\Adapter\AIImageAdapterInterface;
use ModelflowAi\Image\AIImageRequestHandler;
use ModelflowAi\Image\Middleware\HandleMiddleware;
use ModelflowAi\Image\Request\AIImageRequest;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;

require_once \dirname(__DIR__, 2) . '/vendor/autoload.php';

// Load environment for API key
(new Dotenv())->bootEnv(\dirname(__DIR__) . '/.env');

$fireworksaiApiKey = $_ENV['FIREWORKSAI_API_KEY'];
if (!$fireworksaiApiKey) {
    throw new RuntimeException('FireworksAi API key is required');
}

$adapter = [];

// Note: Image generation uses HTTP client instead of the OpenAI-compatible client
$httpClient = HttpClient::create([
    'headers' => [
        'Authorization' => 'Bearer ' . $fireworksaiApiKey,
        'Content-Type' => 'application/json',
    ],
    'base_uri' => 'https://api.fireworks.ai/inference/v1/image_generation/accounts/fireworks/models/',
]);

$stableDiffusion = new FireworksAiImageGenerationAdapter($httpClient);
$adapter[] = new DecisionRule($stableDiffusion, [CapabilityCriteria::BASIC]);

/** @var DecisionTreeInterface<AIImageRequest, AIImageAdapterInterface> $decisionTree */
$decisionTree = new DecisionTree($adapter);

return new AIImageRequestHandler(new HandleMiddleware($decisionTree));
