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
use ModelflowAi\Core\Request\AIChatMessage;
use ModelflowAi\Core\Request\AIChatMessageRoleEnum;
use ModelflowAi\Core\Request\Criteria\PrivacyRequirement;
use ModelflowAi\Core\Response\AIChatResponse;

/** @var AIRequestHandlerInterface $handler */
$handler = require_once __DIR__ . '/bootstrap.php';

/** @var AIChatResponse $response */
$response = $handler->createChatRequest(
    new AIChatMessage(AIChatMessageRoleEnum::SYSTEM, 'You are an angry bot'),
    new AIChatMessage(AIChatMessageRoleEnum::USER, 'Hello world'),
)
    ->addCriteria(PrivacyRequirement::HIGH)
    ->build()
    ->execute();

echo \sprintf('%s: %s', $response->getMessage()->role->value, $response->getMessage()->content);
