<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdapterExtension extends AbstractExtension
{
    /**
     * @param array<string, array{
     *     image_to_text: bool,
     * }> $adapters
     */
    public function __construct(
        private array $adapters,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('image_to_text_enabled', [$this, 'hasImageToTextEnabled']),
        ];
    }

    public function hasImageToTextEnabled(string $adapter): bool
    {
        return $this->adapters[$adapter]['image_to_text'];
    }
}
