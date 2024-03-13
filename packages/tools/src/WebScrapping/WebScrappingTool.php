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

namespace ModelflowAi\Tools\WebScrapping;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * TODO: https://github.com/Significant-Gravitas/AutoGPT/blob/fd2c26188f681ae5afa47105098af4d1202a5562/autogpts/autogpt/autogpt/commands/web_selenium.py.
 */
class WebScrappingTool
{
    public function __construct(
        private readonly HttpClientInterface $client,
    ) {
    }

    /**
     * This tool allows you to open a url directly if one is provided by the user.
     * Only use this tool for this purpose; do not open urls returned by the search function or found on webpages.
     */
    public function getWebPageText(string $url): string
    {
        $response = $this->client->request('GET', $url);
        if (200 !== $response->getStatusCode()) {
            throw new \Exception('Could not fetch the web page');
        }

        $html = $response->getContent();

        $text = (string) \preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $text = (string) \preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $text);
        $text = \strip_tags($text);
        $text = \html_entity_decode($text);
        $text = \str_replace("\n", '.', $text);
        $text = \str_replace("\t", '.', $text);
        $text = \str_replace("\r", '.', $text);
        $text = (string) \preg_replace('/( )+/', ' ', $text);

        return (string) \preg_replace('/((\.)|( \.))+/', '.', $text);
    }
}
