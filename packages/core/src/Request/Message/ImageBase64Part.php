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

namespace ModelflowAi\Core\Request\Message;

use Webmozart\Assert\Assert;

readonly class ImageBase64Part extends MessagePart
{
    public static function create(
        string $path,
    ): self {
        $content = \file_get_contents($path);
        Assert::string($content);

        return new self(\base64_encode($content));
    }

    public function __construct(
        public string $content,
    ) {
        parent::__construct(MessagePartTypeEnum::BASE64_IMAGE);
    }

    public function enhanceMessage(array $message): array
    {
        $message['images'] = \array_merge($message['images'] ?? [], [$this->content]);

        return $message;
    }
}
