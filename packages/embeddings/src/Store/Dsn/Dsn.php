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

namespace ModelflowAi\Embeddings\Store\Dsn;

final readonly class Dsn
{
    /**
     * @param array<string|int, bool|int|string|string[]> $options
     */
    public function __construct(
        public string $scheme,
        public ?string $host,
        public ?string $user = null,
        #[\SensitiveParameter]
        public ?string $password = null,
        public ?int $port = null,
        public ?string $path = null,
        public array $options = [],
    ) {
    }

    public static function fromString(#[\SensitiveParameter] string $dsn): self
    {
        if ('memory://' === $dsn) {
            return new self('memory', null);
        }

        // Special handling for file:// URLs
        if (\str_starts_with($dsn, 'file://')) {
            $path = \substr($dsn, 7);
            // Check if there are any query parameters
            $queryPos = \strpos($path, '?');
            $options = [];

            if (false !== $queryPos) {
                $query = \substr($path, $queryPos + 1);
                $path = \substr($path, 0, $queryPos);
                \parse_str($query, $options);
            }

            return new self('file', null, null, null, null, $path, $options);
        }

        // Standard URL parsing for other schemes
        if (false === $params = \parse_url($dsn)) {
            throw new \InvalidArgumentException('The DSN is invalid.');
        }

        if (!isset($params['scheme'])) {
            throw new \InvalidArgumentException('The DSN must contain a scheme.');
        }

        $user = isset($params['user']) && '' !== $params['user'] ? \rawurldecode($params['user']) : null;
        $password = isset($params['pass']) && '' !== $params['pass'] ? \rawurldecode($params['pass']) : null;
        $port = isset($params['port']) ? (int) $params['port'] : null;
        $path = isset($params['path']) && '' !== $params['path'] ? \ltrim($params['path'], '/') : null;
        $host = $params['host'] ?? null;

        $options = [];
        if (isset($params['query'])) {
            \parse_str($params['query'], $options);
        }

        return new self($params['scheme'], $host, $user, $password, $port, $path, $options);
    }

    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options[$key] ?? $default;
    }

    public function getBooleanOption(string $key, bool $default = false): bool
    {
        return \filter_var($this->getOption($key, $default), \FILTER_VALIDATE_BOOLEAN);
    }
}
