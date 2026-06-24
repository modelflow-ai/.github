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

final readonly class Dimensions
{
    private function __construct(
        public int $dpi,
        public int $height,
        public int $width,
    ) {
    }

    /**
     * @param array<mixed> $attributes
     */
    public static function from(array $attributes): self
    {
        $dpi = $attributes['dpi'] ?? 0;
        $height = $attributes['height'] ?? 0;
        $width = $attributes['width'] ?? 0;

        Assert::integer($dpi);
        Assert::integer($height);
        Assert::integer($width);

        return new self(
            $dpi,
            $height,
            $width,
        );
    }
}
