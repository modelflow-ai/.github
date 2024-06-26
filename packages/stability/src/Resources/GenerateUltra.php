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

namespace ModelflowAi\Stability\Resources;

use ModelflowAi\ApiClient\Resources\Concerns\Streamable;
use ModelflowAi\ApiClient\Transport\Enums\ContentType;
use ModelflowAi\ApiClient\Transport\Payload;
use ModelflowAi\ApiClient\Transport\TransportInterface;
use ModelflowAi\Stability\Responses\Generate\GenerateBase64Response;
use ModelflowAi\Stability\Responses\Generate\GenerateFileResponse;
use Webmozart\Assert\Assert;

final readonly class GenerateUltra implements GenerateUltraInterface
{
    use Streamable;

    public function __construct(
        private TransportInterface $transport,
    ) {
    }

    public function generateAsResource(array $parameters): GenerateFileResponse
    {
        $this->validateParameters($parameters);

        $payload = Payload::create('generate/ultra', $parameters, ContentType::MULTIPART);

        $response = $this->transport->requestRaw($payload);

        return GenerateFileResponse::from($response->resource, $response->meta);
    }

    public function generateAsBase64(array $parameters): GenerateBase64Response
    {
        $this->validateParameters($parameters);

        $payload = Payload::create('generate/ultra', $parameters);

        $response = $this->transport->requestRaw($payload);

        return GenerateBase64Response::from($response->resource, $response->meta);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function validateParameters(array $parameters): void
    {
        Assert::keyExists($parameters, 'prompt');
        Assert::string($parameters['prompt']);

        if (isset($parameters['negative_prompt'])) {
            Assert::string($parameters['negative_prompt']);
        }

        if (isset($parameters['aspect_ratio'])) {
            Assert::inArray($parameters['aspect_ratio'], [
                '16:9',
                '1:1',
                '21:9',
                '2:3',
                '3:2',
                '4:5',
                '5:4',
                '9:16',
                '9:21',
            ]);
        }

        if (isset($parameters['seed'])) {
            Assert::integerish($parameters['seed']);
        }

        if (isset($parameters['output_format'])) {
            Assert::inArray($parameters['output_format'], [
                'jpeg',
                'png',
                'webp',
            ]);
        }
    }
}
