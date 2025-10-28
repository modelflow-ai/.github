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

trait EmbeddingTrait
{
    public static function fromArray(array $data): self
    {
        $class = new \ReflectionClass(static::class);
        $instance = $class->newInstanceWithoutConstructor();

        foreach ($data as $key => $value) {
            if (!$class->hasProperty($key)) {
                continue;
            }

            $property = $class->getProperty($key);
            $property->setAccessible(true);
            $property->setValue($instance, $value);
        }

        return $instance;
    }

    protected string $content;

    protected ?string $formattedContent = null;

    /**
     * @var float[]|null
     */
    protected ?array $vector = null;

    protected string $hash;

    protected int $chunkNumber = 0;

    protected ?float $score = null;

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

    /**
     * @return string[]
     */
    abstract public function getIdentifierParts(): array;

    public function getIdentifier(): string
    {
        return $this->formatUuid(\implode('-', $this->getIdentifierParts()) . '-' . $this->chunkNumber);
    }

    private function formatUuid(string $identifier): string
    {
        // 1. Generate a SHA-256 hash of the data.
        $hash = \hash('sha256', $identifier);

        // 2. Extract portions of the hash to form the UUID.
        $part1 = \substr($hash, 0, 8);
        $part2 = \substr($hash, 8, 4);

        // For parts 3 and 4, we're making adjustments to ensure the UUID is a valid version 5 UUID.
        $part3 = (\hexdec(\substr($hash, 12, 4)) & 0x0FFF) | 0x5000;
        $part4 = (\hexdec(\substr($hash, 16, 4)) & 0x3FFF) | 0x8000;

        $part5 = \substr($hash, 20, 12);

        // 3. Combine the parts to form the UUID.
        return \sprintf('%08s-%04s-%04x-%04x-%12s', $part1, $part2, $part3, $part4, $part5);
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

    public function getScore(): ?float
    {
        return $this->score;
    }

    public function toArray(): array
    {
        $result = [];

        $class = new \ReflectionClass($this);
        foreach ($class->getProperties() as $property) {
            $result[$property->getName()] = $property->getValue($this);
        }

        return $result;
    }

    protected function hash(string $content): string
    {
        return \hash('sha256', $content);
    }
}
