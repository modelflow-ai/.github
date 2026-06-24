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

namespace ModelflowAi\Mistral\Resources;

use ModelflowAi\ApiClient\Transport\Payload;
use ModelflowAi\ApiClient\Transport\TransportInterface;
use ModelflowAi\Mistral\Model;
use ModelflowAi\Mistral\Responses\Ocr\ProcessResponse;
use Webmozart\Assert\Assert;

final readonly class Ocr implements OcrInterface
{
    public function __construct(
        private TransportInterface $transport,
    ) {
    }

    public function process(array $parameters): ProcessResponse
    {
        $this->validateParameters($parameters);
        $parameters['model'] ??= Model::OCR->value;

        $payload = Payload::create('ocr', $parameters);

        $response = $this->transport->requestObject($payload);

        return ProcessResponse::from($response->data, $response->meta);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    private function validateParameters(array $parameters): void
    {
        if (isset($parameters['model'])) {
            Assert::string($parameters['model']);
        }

        Assert::keyExists($parameters, 'document');
        Assert::isArray($parameters['document']);
        $documentType = $parameters['document']['type'] ?? $this->detectDocumentType($parameters['document']);
        Assert::string($documentType);
        Assert::inArray($documentType, ['document_url', 'image_url', 'file']);

        if ('document_url' === $documentType) {
            Assert::keyExists($parameters['document'], 'document_url');
            Assert::string($parameters['document']['document_url']);

            if (isset($parameters['document']['document_name'])) {
                Assert::string($parameters['document']['document_name']);
            }
        }

        if ('image_url' === $documentType) {
            Assert::keyExists($parameters['document'], 'image_url');
            Assert::string($parameters['document']['image_url']);
        }

        if ('file' === $documentType) {
            Assert::keyExists($parameters['document'], 'file_id');
            Assert::string($parameters['document']['file_id']);
        }

        if (isset($parameters['pages'])) {
            if (\is_array($parameters['pages'])) {
                Assert::allInteger($parameters['pages']);
            } else {
                Assert::string($parameters['pages']);
            }
        }

        if (isset($parameters['include_blocks'])) {
            Assert::boolean($parameters['include_blocks']);
        }
        if (isset($parameters['include_image_base64'])) {
            Assert::boolean($parameters['include_image_base64']);
        }
        if (isset($parameters['image_limit'])) {
            Assert::integer($parameters['image_limit']);
        }
        if (isset($parameters['image_min_size'])) {
            Assert::integer($parameters['image_min_size']);
        }
        if (isset($parameters['bbox_annotation_format'])) {
            Assert::isArray($parameters['bbox_annotation_format']);
        }
        if (isset($parameters['document_annotation_format'])) {
            Assert::isArray($parameters['document_annotation_format']);
        }
        if (isset($parameters['document_annotation_prompt'])) {
            Assert::string($parameters['document_annotation_prompt']);
        }
        if (isset($parameters['table_format'])) {
            Assert::string($parameters['table_format']);
            Assert::inArray($parameters['table_format'], ['markdown', 'html']);
        }
        if (isset($parameters['extract_header'])) {
            Assert::boolean($parameters['extract_header']);
        }
        if (isset($parameters['extract_footer'])) {
            Assert::boolean($parameters['extract_footer']);
        }
        if (isset($parameters['confidence_scores_granularity'])) {
            Assert::string($parameters['confidence_scores_granularity']);
            Assert::inArray($parameters['confidence_scores_granularity'], ['word', 'page']);
        }
    }

    /**
     * @param array<mixed> $document
     */
    private function detectDocumentType(array $document): string
    {
        if (isset($document['document_url'])) {
            return 'document_url';
        }

        if (isset($document['image_url'])) {
            return 'image_url';
        }

        if (isset($document['file_id'])) {
            return 'file';
        }

        throw new \InvalidArgumentException('The document type could not be detected.');
    }
}
