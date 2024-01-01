<?php

namespace ModelflowAi\Embeddings\Algorithm;

final class DistanceL2Utils
{
    private function __construct()
    {
    }

    /**
     * @param float[] $vector1
     * @param float[] $vector2
     */
    public static function euclideanDistanceL2(array $vector1, array $vector2): float
    {
        if (count($vector1) !== count($vector2)) {
            throw new \InvalidArgumentException('Arrays must have the same length.');
        }

        $sum = 0.0;
        foreach ($vector1 as $i => $singleVector1) {
            $sum += ($singleVector1 - $vector2[$i]) ** 2;
        }

        return sqrt($sum);
    }
}
