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

use ModelflowAi\Mistral\Responses\Ocr\ProcessResponse;

interface OcrInterface
{
    /**
     * @param array{
     *     model?: string|null,
     *     document: array<string, mixed>,
     *     pages?: string|int[]|null,
     *     include_blocks?: bool|null,
     *     include_image_base64?: bool|null,
     *     image_limit?: int|null,
     *     image_min_size?: int|null,
     *     bbox_annotation_format?: array<string, mixed>|null,
     *     document_annotation_format?: array<string, mixed>|null,
     *     document_annotation_prompt?: string|null,
     *     table_format?: 'markdown'|'html'|null,
     *     extract_header?: bool,
     *     extract_footer?: bool,
     *     confidence_scores_granularity?: 'word'|'page'|null,
     * } $parameters
     */
    public function process(array $parameters): ProcessResponse;
}
