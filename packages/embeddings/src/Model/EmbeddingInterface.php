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

namespace ModelflowAi\Embeddings\Model;

interface EmbeddingInterface
{
    public function split(string $content, int $chunkNumber): self;

    public function getContent(): string;

    public function getFormattedContent(): string;

    public function setFormattedContent(string $formattedContent): void;

    public function getVector(): ?array;

    public function setVector(array $vector): void;

    public function getHash(): string;

    public function getChunkNumber(): int;
}
