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

namespace ModelflowAi\Image\Request\Builder;

use ModelflowAi\Image\Request\Action\TextToImageAction;
use ModelflowAi\Image\Request\AIImageRequest;
use ModelflowAi\Image\Request\Value\ImageFormat;
use ModelflowAi\Image\Request\Value\OutputFormat;

final readonly class TextToImageActionBuilder
{
    public function __construct(
        private AIImageRequestBuilder $builder,
        private string $prompt,
    ) {
    }

    public function imageFormat(ImageFormat $imageFormat): self
    {
        $this->builder->imageFormat($imageFormat);

        return $this;
    }

    public function asStream(): self
    {
        $this->builder->as(OutputFormat::STREAM);

        return $this;
    }

    public function asBase64(): self
    {
        $this->builder->as(OutputFormat::BASE64);

        return $this;
    }

    public function build(): AIImageRequest
    {
        return $this->builder->build(new TextToImageAction($this->prompt));
    }
}
