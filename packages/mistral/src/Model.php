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

namespace ModelflowAi\Mistral;

enum Model: string
{
    case TINY = 'mistral-tiny';
    case SMALL = 'mistral-small-latest';
    case MEDIUM = 'mistral-medium-latest';
    case LARGE = 'mistral-large-latest';
    case NEMO = 'open-mistral-nemo';
    case EMBED = 'mistral-embed';
    case OCR = 'mistral-ocr-latest';
    case OCR_4 = 'mistral-ocr-4-0';
    case PIXTRAL_LARGE = 'pixtral-large-latest';

    public function jsonSupported(): bool
    {
        return \in_array($this, [self::SMALL, self::LARGE], true);
    }

    public function toolsSupported(): bool
    {
        return \in_array($this, [self::SMALL, self::MEDIUM, self::LARGE, self::NEMO, self::PIXTRAL_LARGE], true);
    }
}
