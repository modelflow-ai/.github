<?php

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

    public function search(string $query): array
    {
        $response = $this->client->request('GET', $this->url, [
            'query' => [
                'key' => $this->googleCustomSearchEngineApiKey,
                'cx' => $this->googleCustomSearchEngineId,
                'q' => $query
            ],
        ]);

        $content = \json_decode($response->getContent(), true);

        $result = [];
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
