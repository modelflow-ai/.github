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

namespace ModelflowAi\Mistral\Responses\Ocr;

use Webmozart\Assert\Assert;

final readonly class UsageInfo
{
    private function __construct(
        public int $pagesProcessed,
        public ?int $docSizeBytes,
    ) {
    }

    /**
     * @param array<mixed> $attributes
     */
    public static function from(array $attributes): self
    {
        $pagesProcessed = $attributes['pages_processed'];
        $docSizeBytes = $attributes['doc_size_bytes'] ?? null;

        Assert::integer($pagesProcessed);
        Assert::nullOrInteger($docSizeBytes);

        return new self(
            $pagesProcessed,
            $docSizeBytes,
        );
    }
}
