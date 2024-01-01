<?php

namespace ModelflowAi\Embeddings\Model;

trait EmbeddingTrait
{
    protected string $content;

    protected ?string $formattedContent = null;

    /**
     * @var float[]|null
     */
    protected ?array $vector = null;

    protected string $hash;

    protected int $chunkNumber = 0;

    public function split(string $content, int $chunkNumber): EmbeddingInterface
    {
        $embedding = clone $this;
        $embedding->content = $content;
        $embedding->formattedContent = null;
        $embedding->vector = null;
        $embedding->hash = $this->hash($content);
        $embedding->chunkNumber = $chunkNumber;

        return $embedding;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getFormattedContent(): string
    {
        return $this->formattedContent ?: $this->content;
    }

    public function setFormattedContent(string $formattedContent): void
    {
        $this->formattedContent = $formattedContent;
    }

    public function getVector(): ?array
    {
        return $this->vector;
    }

    public function setVector(array $vector): void
    {
        $this->vector = $vector;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getChunkNumber(): int
    {
        return $this->chunkNumber;
    }

    protected function hash(string $content): string
    {
        return hash('sha256', $content);
    }
}
