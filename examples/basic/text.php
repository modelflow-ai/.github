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

namespace App;

use ModelflowAi\Core\AIRequestHandlerInterface;
use ModelflowAi\Core\Request\Criteria\PrivacyCriteria;
use ModelflowAi\Core\Response\AICompletionResponse;
use ModelflowAi\PromptTemplate\PromptTemplate;

/** @var AIRequestHandlerInterface $handler */
$handler = require_once __DIR__ . '/bootstrap.php';

/** @var AICompletionResponse $response */
$response = $handler->createTextRequest(PromptTemplate::create('Hello {where}!')->format(['where' => 'world']))
    ->addCriteria(PrivacyCriteria::HIGH)
    ->build()
    ->execute();

echo $response->getContent();
