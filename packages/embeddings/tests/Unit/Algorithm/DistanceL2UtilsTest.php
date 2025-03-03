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

namespace ModelflowAi\Embeddings\Tests\Unit\Algorithm;

use ModelflowAi\Embeddings\Algorithm\DistanceL2Utils;
use PHPUnit\Framework\TestCase;

class DistanceL2UtilsTest extends TestCase
{
    public function testEuclideanDistanceL2WithIdenticalVectors(): void
    {
        $vector1 = [1.0, 2.0, 3.0, 4.0, 5.0];
        $vector2 = [1.0, 2.0, 3.0, 4.0, 5.0];

        $distance = DistanceL2Utils::euclideanDistanceL2($vector1, $vector2);

        $this->assertSame(0.0, $distance);
    }

    public function testEuclideanDistanceL2WithDifferentVectors(): void
    {
        $vector1 = [1.0, 0.0, 0.0];
        $vector2 = [0.0, 1.0, 0.0];

        // The Euclidean distance between [1,0,0] and [0,1,0] should be sqrt(2)
        $distance = DistanceL2Utils::euclideanDistanceL2($vector1, $vector2);

        $this->assertSame(\sqrt(2), $distance);
    }

    public function testEuclideanDistanceL2WithNegativeValues(): void
    {
        $vector1 = [1.0, -2.0, 3.0];
        $vector2 = [-1.0, 2.0, -3.0];

        // Manually calculate expected distance
        // (1-(-1))^2 + (-2-2)^2 + (3-(-3))^2 = 4 + 16 + 36 = 56
        // sqrt(56) = 7.483314774
        $expected = \sqrt(56);
        $distance = DistanceL2Utils::euclideanDistanceL2($vector1, $vector2);

        $this->assertSame($expected, $distance);
    }

    public function testEuclideanDistanceL2WithEmptyVectors(): void
    {
        $vector1 = [];
        $vector2 = [];

        $distance = DistanceL2Utils::euclideanDistanceL2($vector1, $vector2);

        $this->assertSame(0.0, $distance);
    }

    public function testEuclideanDistanceL2ThrowsExceptionForDifferentLengthVectors(): void
    {
        $vector1 = [1.0, 2.0, 3.0];
        $vector2 = [1.0, 2.0];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Arrays must have the same length.');

        DistanceL2Utils::euclideanDistanceL2($vector1, $vector2);
    }

    public function testEuclideanDistanceL2WithLargeVectors(): void
    {
        // Generate large vectors (1000 elements each)
        $vector1 = \array_fill(0, 1000, 0.5);
        $vector2 = \array_fill(0, 1000, 1.0);

        // Manually calculate expected distance
        // Sum of (0.5 - 1.0)^2 1000 times = 1000 * 0.25 = 250
        // sqrt(250) ≈ 15.8113883008
        $expected = \sqrt(250);
        $distance = DistanceL2Utils::euclideanDistanceL2($vector1, $vector2);

        $this->assertSame($expected, $distance);
    }

    public function testEuclideanDistanceL2WithPrecisionEdgeCases(): void
    {
        // Test with very small differences
        $vector1 = [0.0000001];
        $vector2 = [0.0000002];

        $expected = 0.0000001;
        $distance = DistanceL2Utils::euclideanDistanceL2($vector1, $vector2);

        $this->assertSame($expected, $distance);

        // Test with very large numbers
        $vector3 = [1_000_000.0];
        $vector4 = [1_000_001.0];

        $expected = 1.0;
        $distance = DistanceL2Utils::euclideanDistanceL2($vector3, $vector4);

        $this->assertSame($expected, $distance);
    }

    public function testEuclideanDistanceL2WithFloatingPointPrecision(): void
    {
        $vector1 = [0.1, 0.2, 0.3];
        $vector2 = [0.4, 0.5, 0.6];

        // (0.4-0.1)^2 + (0.5-0.2)^2 + (0.6-0.3)^2 = 0.09 + 0.09 + 0.09 = 0.27
        // sqrt(0.27) ≈ 0.5196152423
        $expected = \sqrt(0.27);
        $distance = DistanceL2Utils::euclideanDistanceL2($vector1, $vector2);

        $this->assertSame($expected, $distance);
    }
}
