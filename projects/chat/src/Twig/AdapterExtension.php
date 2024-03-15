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
            new TwigFunction('feature_enabled', [$this, 'featureEnabled']),
        ];
    }

    public function featureEnabled(string $adapter, string $feature): bool
    {
        return $this->adapters[$adapter][$feature];
    }
}
