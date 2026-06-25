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

namespace ModelflowAi\Mistral\Tests\Unit\Resources;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\ApiClient\Transport\Enums\Method;
use ModelflowAi\ApiClient\Transport\Payload;
use ModelflowAi\ApiClient\Transport\Response\ObjectResponse;
use ModelflowAi\ApiClient\Transport\TransportInterface;
use ModelflowAi\Mistral\Model;
use ModelflowAi\Mistral\Resources\Ocr;
use ModelflowAi\Mistral\Resources\OcrInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

final class OcrTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<TransportInterface>
     */
    private ObjectProphecy $transport;

    protected function setUp(): void
    {
        $this->transport = $this->prophesize(TransportInterface::class);
    }

    public function testProcess(): void
    {
        $response = new ObjectResponse([
            'pages' => [
                [
                    'index' => 0,
                    'markdown' => '# Invoice',
                    'images' => [],
                    'dimensions' => null,
                    'blocks' => [
                        [
                            'type' => 'title',
                            'top_left_x' => 10,
                            'top_left_y' => 20,
                            'bottom_right_x' => 120,
                            'bottom_right_y' => 60,
                            'content' => 'Invoice',
                        ],
                    ],
                ],
            ],
            'model' => Model::OCR_4->value,
            'usage_info' => [
                'pages_processed' => 1,
                'doc_size_bytes' => 1234,
            ],
        ], MetaInformation::from([]));

        $this->transport->requestObject(
            Argument::that(static fn (Payload $payload) => 'ocr' === $payload->resourceUri->uri
                && Method::POST === $payload->method
                && [
                    'model' => Model::OCR_4->value,
                    'document' => [
                        'type' => 'document_url',
                        'document_url' => 'https://example.com/invoice.pdf',
                        'document_name' => 'invoice.pdf',
                    ],
                    'pages' => '0,1',
                    'include_blocks' => true,
                    'include_image_base64' => true,
                    'table_format' => 'markdown',
                ] === $payload->parameters),
        )->willReturn($response);

        $ocr = $this->createInstance($this->transport->reveal());

        $result = $ocr->process([
            'model' => Model::OCR_4->value,
            'document' => [
                'type' => 'document_url',
                'document_url' => 'https://example.com/invoice.pdf',
                'document_name' => 'invoice.pdf',
            ],
            'pages' => '0,1',
            'include_blocks' => true,
            'include_image_base64' => true,
            'table_format' => 'markdown',
        ]);

        $this->assertSame(Model::OCR_4->value, $result->model);
        $this->assertSame('title', $result->pages[0]->blocks[0]->type);
    }

    public function testProcessUsesDefaultModel(): void
    {
        $response = new ObjectResponse([
            'pages' => [
                [
                    'index' => 0,
                    'markdown' => '# Invoice',
                    'images' => [],
                    'dimensions' => null,
                ],
            ],
            'model' => Model::OCR->value,
            'usage_info' => [
                'pages_processed' => 1,
            ],
        ], MetaInformation::from([]));

        $this->transport->requestObject(
            Argument::that(static fn (Payload $payload) => Model::OCR->value === $payload->parameters['model']),
        )->willReturn($response);

        $ocr = $this->createInstance($this->transport->reveal());

        $result = $ocr->process([
            'document' => [
                'document_url' => 'https://example.com/invoice.pdf',
            ],
        ]);

        $this->assertSame(Model::OCR->value, $result->model);
    }

    public function testProcessWritesDetectedDocumentType(): void
    {
        $response = new ObjectResponse([
            'pages' => [
                [
                    'index' => 0,
                    'markdown' => '# Invoice',
                    'images' => [],
                    'dimensions' => null,
                ],
            ],
            'model' => Model::OCR->value,
            'usage_info' => [
                'pages_processed' => 1,
            ],
        ], MetaInformation::from([]));

        $capturedType = null;
        $this->transport->requestObject(
            Argument::that(static function (Payload $payload) use (&$capturedType): bool {
                $document = $payload->parameters['document'] ?? null;
                $capturedType = \is_array($document) ? ($document['type'] ?? null) : null;

                return true;
            }),
        )->willReturn($response);

        $ocr = $this->createInstance($this->transport->reveal());

        $ocr->process([
            'document' => [
                'document_url' => 'https://example.com/invoice.pdf',
            ],
        ]);

        $this->assertSame('document_url', $capturedType);
    }

    public function testProcessMissingDocumentSource(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $ocr = $this->createInstance($this->transport->reveal());

        $ocr->process(['document' => []]);
    }

    private function createInstance(TransportInterface $transport): OcrInterface
    {
        return new Ocr($transport);
    }
}
