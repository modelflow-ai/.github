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

final readonly class ConfidenceScore
{
    private function __construct(
        public string $text,
        public float $confidence,
        public int $startIndex,
    ) {
    }

    /**
     * @param array<mixed> $attributes
     */
    public static function from(array $attributes): self
    {
        $text = $attributes['text'];
        $confidence = $attributes['confidence'];
        $startIndex = $attributes['start_index'];

        Assert::string($text);
        Assert::numeric($confidence);
        Assert::integer($startIndex);

        return new self(
            $text,
            (float) $confidence,
            $startIndex,
        );
    }
}
