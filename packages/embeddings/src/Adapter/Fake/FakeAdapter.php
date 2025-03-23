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

namespace ModelflowAi\Embeddings\Adapter\Fake;

use ModelflowAi\Embeddings\Adapter\EmbeddingAdapterInterface;
use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;
use ModelflowAi\Embeddings\Adapter\Response\EmbedResponse;
use ModelflowAi\Embeddings\Usage\EmbeddingUsage;

class FakeAdapter implements EmbeddingAdapterInterface
{
    /**
     * @param array<string, float[]> $embeddings
     */
    public function __construct(
        private readonly array $embeddings,
    ) {
    }

    public function embedText(string $text): array
    {
        if (!\array_key_exists($text, $this->embeddings)) {
            throw new \RuntimeException(\sprintf('Text "%s" not found in embeddings.', $text));
        }

        return $this->embeddings[$text];
    }

    public function embed(EmbedRequest $request): EmbedResponse
    {
        $text = $request->getText();
        $vector = $this->embedText($text);

        return new EmbedResponse(
            $vector,
            new EmbeddingUsage(\strlen($text)),
        );
    }
}
