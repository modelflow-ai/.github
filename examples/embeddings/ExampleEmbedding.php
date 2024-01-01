<?php

namespace App;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;
use ModelflowAi\Embeddings\Model\EmbeddingTrait;

class ExampleEmbedding implements EmbeddingInterface
{
    use EmbeddingTrait;

    private string $fileName;

    /**
     * @param string $fileName
     */
    public function __construct(string $content, string $fileName)
    {
        $this->fileName = $fileName;
        $this->content = $content;
        $this->hash = $this->hash($fileName);
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }
}
