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

final readonly class Page
{
    /**
     * @param Image[] $images
     * @param Table[] $tables
     * @param string[] $hyperlinks
     * @param Block[] $blocks
     */
    private function __construct(
        public int $index,
        public string $markdown,
        public array $images,
        public ?Dimensions $dimensions,
        public array $tables,
        public array $hyperlinks,
        public ?string $header,
        public ?string $footer,
        public ?ConfidenceScores $confidenceScores,
        public array $blocks,
    ) {
    }

    /**
     * @param array<mixed> $attributes
     */
    public static function from(array $attributes): self
    {
        $index = $attributes['index'];
        $markdown = $attributes['markdown'];
        $rawImages = $attributes['images'];
        $rawDimensions = $attributes['dimensions'];
        $rawTables = $attributes['tables'] ?? [];
        $hyperlinks = $attributes['hyperlinks'] ?? [];
        $header = $attributes['header'] ?? null;
        $footer = $attributes['footer'] ?? null;
        $rawConfidenceScores = $attributes['confidence_scores'] ?? null;
        $rawBlocks = $attributes['blocks'] ?? [];

        Assert::integer($index);
        Assert::string($markdown);
        Assert::isArray($rawImages);
        Assert::nullOrIsArray($rawDimensions);
        Assert::isArray($rawTables);
        Assert::isArray($hyperlinks);
        Assert::allString($hyperlinks);
        Assert::nullOrString($header);
        Assert::nullOrString($footer);
        Assert::nullOrIsArray($rawConfidenceScores);
        Assert::isArray($rawBlocks);

        $images = \array_map(
            static function (mixed $image): Image {
                Assert::isArray($image);

                return Image::from($image);
            },
            $rawImages,
        );

        $tables = \array_map(
            static function (mixed $table): Table {
                Assert::isArray($table);

                return Table::from($table);
            },
            $rawTables,
        );

        $blocks = \array_map(
            static function (mixed $block): Block {
                Assert::isArray($block);

                return Block::from($block);
            },
            $rawBlocks,
        );

        return new self(
            $index,
            $markdown,
            $images,
            null !== $rawDimensions ? Dimensions::from($rawDimensions) : null,
            $tables,
            $hyperlinks,
            $header,
            $footer,
            null !== $rawConfidenceScores ? ConfidenceScores::from($rawConfidenceScores) : null,
            $blocks,
        );
    }
}
