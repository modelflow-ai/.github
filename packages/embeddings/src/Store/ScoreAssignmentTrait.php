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

namespace ModelflowAi\Embeddings\Store;

use ModelflowAi\Embeddings\Model\EmbeddingInterface;

/**
 * Trait for safely assigning similarity scores to embedding instances.
 *
 * This trait provides a cached, reusable method for setting the protected
 * score property on embeddings using reflection. It improves performance
 * by caching the ReflectionProperty instance and includes proper error
 * handling for robustness.
 */
trait ScoreAssignmentTrait
{
    /**
     * Cache for ReflectionProperty instances per class.
     *
     * @var array<class-string, \ReflectionProperty>
     */
    private static array $scorePropertyCache = [];

    /**
     * Safely assign a similarity score to an embedding instance.
     *
     * This method uses reflection to set the protected 'score' property on
     * an embedding. The ReflectionProperty is cached per class for performance.
     *
     * @param EmbeddingInterface $embedding The embedding instance to modify
     * @param float $score The similarity score to assign (typically 0-1 range)
     *
     * @throws \RuntimeException If the score property cannot be accessed
     */
    private function assignScore(EmbeddingInterface $embedding, float $score): void
    {
        $class = $embedding::class;

        // Get or create cached ReflectionProperty for this class
        if (!isset(self::$scorePropertyCache[$class])) {
            try {
                $property = new \ReflectionProperty($embedding, 'score');
            } catch (\ReflectionException $e) {
                throw new \RuntimeException(
                    \sprintf('Cannot access score property on class %s: %s', $class, $e->getMessage()),
                    0,
                    $e,
                );
            }

            self::$scorePropertyCache[$class] = $property;
        }

        $property = self::$scorePropertyCache[$class];

        // Set the score value
        try {
            $property->setValue($embedding, $score);
        } catch (\ReflectionException $e) {
            throw new \RuntimeException(
                \sprintf('Failed to set score on class %s: %s', $class, $e->getMessage()),
                0,
                $e,
            );
        }
    }
}
