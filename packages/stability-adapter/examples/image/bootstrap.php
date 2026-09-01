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

require_once \dirname(__DIR__, 2) . '/vendor/autoload.php';

use ModelflowAi\DecisionTree\Criteria\CapabilityCriteria;
use ModelflowAi\DecisionTree\DecisionRule;
use ModelflowAi\DecisionTree\DecisionTree;
use ModelflowAi\DecisionTree\DecisionTreeInterface;
use ModelflowAi\Image\Adapter\AIImageAdapterInterface;
use ModelflowAi\Image\AIImageRequestHandler;
use ModelflowAi\Image\Middleware\HandleMiddleware;
use ModelflowAi\Image\Request\AIImageRequest;
use ModelflowAi\Stability\Stability;
use ModelflowAi\StabilityAdapter\Image\StabilityImageAdapter;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$stabilityClient = Stability::client($_ENV['STABILITY_API_KEY']);

$adapter = [];

$stabilityAdapter = new StabilityImageAdapter($stabilityClient);
$adapter[] = new DecisionRule($stabilityAdapter, [CapabilityCriteria::BASIC]);

/** @var DecisionTreeInterface<AIImageRequest, AIImageAdapterInterface> $decisionTree */
$decisionTree = new DecisionTree($adapter);

return new AIImageRequestHandler(new HandleMiddleware($decisionTree));
