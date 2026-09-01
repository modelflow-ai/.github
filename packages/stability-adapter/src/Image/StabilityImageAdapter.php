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

namespace ModelflowAi\StabilityAdapter\Image;

use ModelflowAi\Image\Adapter\AIImageAdapterInterface;
use ModelflowAi\Image\Request\Action\TextToImageAction;
use ModelflowAi\Image\Request\AIImageRequest;
use ModelflowAi\Image\Request\Value\ImageFormat;
use ModelflowAi\Image\Request\Value\OutputFormat;
use ModelflowAi\Image\Response\AIImageResponse;
use ModelflowAi\Stability\ClientInterface;

class StabilityImageAdapter implements AIImageAdapterInterface
{
    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    public function handleRequest(AIImageRequest $request): AIImageResponse
    {
        /** @var TextToImageAction $action */
        $action = $request->action;

        $parameters = [
            'prompt' => $action->prompt,
        ];

        // Map output format
        if (OutputFormat::BASE64 === $request->outputFormat) {
            $response = $this->client->generateUltra()->generateAsBase64($parameters);

            return new AIImageResponse($request, ImageFormat::PNG, $response->base64);
        }

        $response = $this->client->generateUltra()->generateAsResource($parameters);

        return new AIImageResponse($request, ImageFormat::PNG, $response->resource);
    }

    public function supports(object $request): bool
    {
        if (!$request instanceof AIImageRequest) {
            return false;
        }

        return $request->action instanceof TextToImageAction;
    }
}
