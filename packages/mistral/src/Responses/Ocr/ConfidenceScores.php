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

final readonly class ConfidenceScores
{
    /**
     * @param ConfidenceScore[] $wordConfidenceScores
     */
    private function __construct(
        public array $wordConfidenceScores,
        public float $averagePageConfidenceScore,
        public float $minimumPageConfidenceScore,
    ) {
    }

    /**
     * @param array<mixed> $attributes
     */
    public static function from(array $attributes): self
    {
        $rawWordConfidenceScores = $attributes['word_confidence_scores'] ?? [];
        $averagePageConfidenceScore = $attributes['average_page_confidence_score'];
        $minimumPageConfidenceScore = $attributes['minimum_page_confidence_score'];

        Assert::isArray($rawWordConfidenceScores);
        Assert::numeric($averagePageConfidenceScore);
        Assert::numeric($minimumPageConfidenceScore);
        $averagePageConfidenceScore = (float) $averagePageConfidenceScore;
        $minimumPageConfidenceScore = (float) $minimumPageConfidenceScore;

        $wordConfidenceScores = \array_map(
            static function (mixed $score): ConfidenceScore {
                Assert::isArray($score);

                return ConfidenceScore::from($score);
            },
            $rawWordConfidenceScores,
        );

        return new self(
            $wordConfidenceScores,
            $averagePageConfidenceScore,
            $minimumPageConfidenceScore,
        );
    }
}
