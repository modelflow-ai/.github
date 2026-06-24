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

final readonly class Table
{
    /**
     * @param ConfidenceScore[] $wordConfidenceScores
     */
    private function __construct(
        public string $id,
        public string $content,
        public string $format,
        public array $wordConfidenceScores,
    ) {
    }

    /**
     * @param array<mixed> $attributes
     */
    public static function from(array $attributes): self
    {
        $id = $attributes['id'];
        $content = $attributes['content'];
        $format = $attributes['format'];
        $rawWordConfidenceScores = $attributes['word_confidence_scores'] ?? [];

        Assert::string($id);
        Assert::string($content);
        Assert::string($format);
        Assert::isArray($rawWordConfidenceScores);

        $wordConfidenceScores = \array_map(
            static function (mixed $score): ConfidenceScore {
                Assert::isArray($score);

                return ConfidenceScore::from($score);
            },
            $rawWordConfidenceScores,
        );

        return new self(
            $id,
            $content,
            $format,
            $wordConfidenceScores,
        );
    }
}
