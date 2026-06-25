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

final readonly class Block
{
    private function __construct(
        public string $type,
        public ?int $topLeftX,
        public ?int $topLeftY,
        public ?int $bottomRightX,
        public ?int $bottomRightY,
        public string $content,
        public ?string $tableId,
        public ?string $imageId,
    ) {
    }

    /**
     * @param array<mixed> $attributes
     */
    public static function from(array $attributes): self
    {
        $type = $attributes['type'];
        $topLeftX = $attributes['top_left_x'] ?? null;
        $topLeftY = $attributes['top_left_y'] ?? null;
        $bottomRightX = $attributes['bottom_right_x'] ?? null;
        $bottomRightY = $attributes['bottom_right_y'] ?? null;
        $content = $attributes['content'] ?? '';
        $tableId = $attributes['table_id'] ?? null;
        $imageId = $attributes['image_id'] ?? null;

        Assert::string($type);
        Assert::nullOrInteger($topLeftX);
        Assert::nullOrInteger($topLeftY);
        Assert::nullOrInteger($bottomRightX);
        Assert::nullOrInteger($bottomRightY);
        Assert::string($content);
        Assert::nullOrString($tableId);
        Assert::nullOrString($imageId);

        return new self(
            $type,
            $topLeftX,
            $topLeftY,
            $bottomRightX,
            $bottomRightY,
            $content,
            $tableId,
            $imageId,
        );
    }
}
