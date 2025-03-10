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

namespace ModelflowAi\Embeddings\Adapter;

use ModelflowAi\Embeddings\Adapter\Request\EmbedRequest;

trait DeprecatedEmbedTextTrait
{
    public function embedText(string $text): array
    {
        trigger_deprecation('modelflow-ai/embeddings', '0.4.0', 'The "%s::embedText" method is deprecated, use "%s::embed" instead.', static::class, static::class);

        $response = $this->embed(new EmbedRequest($text));

        return $response->getVector();
    }
}
