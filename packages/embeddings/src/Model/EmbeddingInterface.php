<?php

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
