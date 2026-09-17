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

require_once __DIR__ . '/../vendor/autoload.php';

use ModelflowAi\FireworksAiAdapter\ClientFactory;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__ . '/.env');

$fireworksaiApiKey = $_ENV['FIREWORKSAI_API_KEY'];
if (!$fireworksaiApiKey) {
    throw new RuntimeException('FireworksAi API key is required');
}

return ClientFactory::create()
    ->withApiKey($fireworksaiApiKey)
    ->make();
