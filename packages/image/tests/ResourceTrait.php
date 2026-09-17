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

namespace ModelflowAi\Image\Tests;

trait ResourceTrait
{
    /**
     * @return resource
     */
    public function getDogImageResource()
    {
        $fileName = __DIR__ . '/../examples/resources/dog.jpeg';
        $file = \fopen($fileName, 'r');
        if (!$file) {
            throw new \RuntimeException('Could not open image "dog.jpeg"');
        }

        return $file;
    }

    /**
     * @return resource
     */
    public function getCatImageResource()
    {
        $fileName = __DIR__ . '/../examples/resources/cat.png';
        $file = \fopen($fileName, 'r');
        if (!$file) {
            throw new \RuntimeException('Could not open image "cat.png"');
        }

        return $file;
    }

    public function getDogImageBase64(): string
    {
        return \base64_encode((string) \stream_get_contents($this->getDogImageResource()));
    }

    public function getCatImageBase64(): string
    {
        return \base64_encode((string) \stream_get_contents($this->getCatImageResource()));
    }
}
