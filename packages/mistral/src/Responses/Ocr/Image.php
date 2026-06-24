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

final readonly class Image
{
    private function __construct(
        public string $id,
        public ?int $topLeftX,
        public ?int $topLeftY,
        public ?int $bottomRightX,
        public ?int $bottomRightY,
        public ?string $imageBase64,
        public ?string $imageAnnotation,
    ) {
    }

    /**
     * @param array<mixed> $attributes
     */
    public static function from(array $attributes): self
    {
        $id = $attributes['id'];
        $topLeftX = $attributes['top_left_x'];
        $topLeftY = $attributes['top_left_y'];
        $bottomRightX = $attributes['bottom_right_x'];
        $bottomRightY = $attributes['bottom_right_y'];
        $imageBase64 = $attributes['image_base64'] ?? null;
        $imageAnnotation = $attributes['image_annotation'] ?? null;

        Assert::string($id);
        Assert::nullOrInteger($topLeftX);
        Assert::nullOrInteger($topLeftY);
        Assert::nullOrInteger($bottomRightX);
        Assert::nullOrInteger($bottomRightY);
        Assert::nullOrString($imageBase64);
        Assert::nullOrString($imageAnnotation);

        return new self(
            $id,
            $topLeftX,
            $topLeftY,
            $bottomRightX,
            $bottomRightY,
            $imageBase64,
            $imageAnnotation,
        );
    }
}
