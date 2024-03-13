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

namespace ModelflowAi\Tools\GoogleSearch;

use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class GoogleSearchTool
{
    public function __construct(
        private HttpClientInterface $client,
        private string $googleCustomSearchEngineId,
        private string $googleCustomSearchEngineApiKey,
        private string $url = 'https://www.googleapis.com/customsearch/v1',
    ) {
    }

    /**
     * Use this tool in the following circumstances:
     * - User is asking about current events or something that requires real-time information (weather, sports scores, etc.)
     * - User is asking about some term you are totally unfamiliar with (it might be new)
     * - User explicitly asks you to browse or provide links to references
     *
     * @return array<array{
     *     title: string,
     *     link: string,
     *     snippet: string,
     * }>
     */
    public function search(string $query): array
    {
        $response = $this->client->request('GET', $this->url, [
            'query' => [
                'key' => $this->googleCustomSearchEngineApiKey,
                'cx' => $this->googleCustomSearchEngineId,
                'q' => $query,
            ],
        ]);

        /** @var array{items: array<string, string>} $content */
        $content = \json_decode($response->getContent(), true);

        $result = [];
        /** @var array{title: string, link: string, snippet: string} $item */
        foreach ($content['items'] as $item) {
            $result[] = [
                'title' => $item['title'],
                'link' => $item['link'],
                'snippet' => $item['snippet'],
            ];
        }

        return $result;
    }
}
