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

namespace ModelflowAi\Chat\Middleware\ResponseFormat;

use ModelflowAi\Chat\Adapter\AIChatAdapterInterface;
use ModelflowAi\Chat\Exception\UnsupportedResponseFormatException;
use ModelflowAi\Chat\Middleware\AIChatMiddlewareInterface;
use ModelflowAi\Chat\Request\AIChatRequest;
use ModelflowAi\Chat\Request\ResponseFormat\JsonSchemaResponseFormat;
use ModelflowAi\Chat\Request\ResponseFormat\ResponseFormatInterface;
use ModelflowAi\Chat\Request\ResponseFormat\SupportsResponseFormatInterface;
use ModelflowAi\Chat\Response\AIChatResponseInterface;
use Webmozart\Assert\Assert;

/**
 * Middleware that handles response format compatibility.
 */
final readonly class ResponseFormatMiddleware implements AIChatMiddlewareInterface
{
    public function process(AIChatRequest $request, ?AIChatAdapterInterface $adapter, callable $next): AIChatResponseInterface
    {
        if (!$adapter instanceof AIChatAdapterInterface) {
            return $next($request, $adapter);
        }

        $responseFormat = $request->getResponseFormat();
        if ($responseFormat instanceof ResponseFormatInterface) {
            Assert::isInstanceOf($responseFormat, ResponseFormatInterface::class);

            if (!$adapter instanceof SupportsResponseFormatInterface
                || !$adapter->supportsResponseFormat($responseFormat)
            ) {
                if ($responseFormat instanceof JsonSchemaResponseFormat) {
                    throw UnsupportedResponseFormatException::forAdapter($responseFormat, $adapter);
                }

                $request->getMessages()->addResponseFormat($responseFormat);
            }
        }

        return $next($request, $adapter);
    }
}
