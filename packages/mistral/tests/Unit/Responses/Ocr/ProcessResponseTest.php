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

namespace ModelflowAi\Mistral\Tests\Unit\Responses\Ocr;

use ModelflowAi\ApiClient\Responses\MetaInformation;
use ModelflowAi\Mistral\Model;
use ModelflowAi\Mistral\Responses\Ocr\ProcessResponse;
use PHPUnit\Framework\TestCase;

final class ProcessResponseTest extends TestCase
{
    public function testFrom(): void
    {
        $instance = ProcessResponse::from([
            'pages' => [
                [
                    'index' => 0,
                    'markdown' => '# Invoice',
                    'images' => [
                        [
                            'id' => 'img-0.jpeg',
                            'top_left_x' => 10,
                            'top_left_y' => 20,
                            'bottom_right_x' => 30,
                            'bottom_right_y' => 40,
                            'image_base64' => 'data:image/jpeg;base64,test',
                        ],
                    ],
                    'dimensions' => [
                        'dpi' => 200,
                        'height' => 2200,
                        'width' => 1700,
                    ],
                    'tables' => [
                        [
                            'id' => 'tbl-0',
                            'content' => '| A | B |',
                            'format' => 'markdown',
                            'word_confidence_scores' => [
                                [
                                    'text' => 'A',
                                    'confidence' => 0.98,
                                    'start_index' => 1,
                                ],
                            ],
                        ],
                    ],
                    'hyperlinks' => ['https://example.com'],
                    'header' => 'Header',
                    'footer' => 'Footer',
                    'confidence_scores' => [
                        'word_confidence_scores' => [
                            [
                                'text' => 'Invoice',
                                'confidence' => 0.99,
                                'start_index' => 0,
                            ],
                        ],
                        'average_page_confidence_score' => 0.99,
                        'minimum_page_confidence_score' => 0.98,
                    ],
                    'blocks' => [
                        [
                            'type' => 'table',
                            'top_left_x' => 10,
                            'top_left_y' => 20,
                            'bottom_right_x' => 120,
                            'bottom_right_y' => 60,
                            'content' => '| A | B |',
                            'table_id' => 'tbl-0',
                        ],
                    ],
                ],
            ],
            'model' => Model::OCR_4->value,
            'usage_info' => [
                'pages_processed' => 1,
                'doc_size_bytes' => 1234,
            ],
            'document_annotation' => '{"invoice":true}',
        ], MetaInformation::from([]));

        $this->assertSame(Model::OCR_4->value, $instance->model);
        $this->assertCount(1, $instance->pages);
        $this->assertSame('# Invoice', $instance->pages[0]->markdown);
        $this->assertSame('img-0.jpeg', $instance->pages[0]->images[0]->id);
        $this->assertSame(200, $instance->pages[0]->dimensions?->dpi);
        $this->assertSame('tbl-0', $instance->pages[0]->tables[0]->id);
        $this->assertSame('https://example.com', $instance->pages[0]->hyperlinks[0]);
        $this->assertSame('Header', $instance->pages[0]->header);
        $this->assertSame('Footer', $instance->pages[0]->footer);
        $this->assertSame(0.99, $instance->pages[0]->confidenceScores?->averagePageConfidenceScore);
        $this->assertSame('table', $instance->pages[0]->blocks[0]->type);
        $this->assertSame(120, $instance->pages[0]->blocks[0]->bottomRightX);
        $this->assertSame('tbl-0', $instance->pages[0]->blocks[0]->tableId);
        $this->assertSame(1, $instance->usageInfo->pagesProcessed);
        $this->assertSame(1234, $instance->usageInfo->docSizeBytes);
        $this->assertSame('{"invoice":true}', $instance->documentAnnotation);
        $this->assertSame([], $instance->meta->headers);
    }

    public function testFromAcceptsIntegerConfidenceScores(): void
    {
        $instance = ProcessResponse::from([
            'pages' => [
                [
                    'index' => 0,
                    'markdown' => '# Invoice',
                    'images' => [],
                    'dimensions' => null,
                    'confidence_scores' => [
                        'word_confidence_scores' => [
                            [
                                'text' => 'Invoice',
                                'confidence' => 1,
                                'start_index' => 0,
                            ],
                        ],
                        'average_page_confidence_score' => 1,
                        'minimum_page_confidence_score' => 0,
                    ],
                ],
            ],
            'model' => Model::OCR->value,
            'usage_info' => [
                'pages_processed' => 1,
            ],
        ], MetaInformation::from([]));

        $confidenceScores = $instance->pages[0]->confidenceScores;
        $this->assertNotNull($confidenceScores);
        $this->assertSame(1.0, $confidenceScores->averagePageConfidenceScore);
        $this->assertSame(0.0, $confidenceScores->minimumPageConfidenceScore);
        $this->assertSame(1.0, $confidenceScores->wordConfidenceScores[0]->confidence);
    }

    public function testFromHandlesMissingOptionalPageAndImageKeys(): void
    {
        $instance = ProcessResponse::from([
            'pages' => [
                [
                    'index' => 0,
                    'markdown' => '# Invoice',
                    'images' => [
                        [
                            'id' => 'img-0.jpeg',
                        ],
                    ],
                ],
            ],
            'model' => Model::OCR->value,
            'usage_info' => [
                'pages_processed' => 1,
            ],
        ], MetaInformation::from([]));

        $this->assertNull($instance->pages[0]->dimensions);
        $this->assertNull($instance->pages[0]->images[0]->topLeftX);
        $this->assertNull($instance->pages[0]->images[0]->bottomRightY);
    }
}
