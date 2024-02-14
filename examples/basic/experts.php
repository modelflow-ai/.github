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

namespace App;

use ModelflowAi\Core\AIRequestHandlerInterface;
use ModelflowAi\Experts\Expert;
use ModelflowAi\Experts\ResponseFormat\JsonSchemaResponseFormat;
use ModelflowAi\Experts\ThreadFactory;

/** @var AIRequestHandlerInterface $handler */
$handler = require_once __DIR__ . '/bootstrap.php';

$expert = new Expert(
    'SEO-Expert',
    'Expert for SEO',
    <<<PROMPT
You are an SEO expert tasked with analyzing the content of a webpage and generating crucial SEO information to enhance its visibility and searchability online. Your analysis and recommendations will be structured according to a specific JSON schema designed for SEO information, which includes the webpage's title, a brief description, and relevant keywords.

Given the content of a webpage, your role is to:

1. Identify the most relevant and impactful title that accurately reflects the core subject or theme of the webpage. This title should be optimized for SEO, making it compelling for both search engines and potential visitors.

2. Craft a concise, engaging description of the webpage's content. This description should summarize the webpage in a way that is informative and appealing, highlighting the unique value or information it provides.

3. Generate a list of keywords that are closely related to the webpage's content. These keywords should be specific and targeted, representing the primary topics or themes discussed on the page. They play a crucial role in improving the webpage's search engine rankings for those terms.
PROMPT,
    [
        ProviderCriteria::OLLAMA,
    ],
    new JsonSchemaResponseFormat([
        'type' => 'object',
        'properties' => [
            'title' => [
                'type' => 'string',
                'description' => 'The title of the webpage, important for SEO and the browser tab',
                'required' => true,
            ],
            'description' => [
                'type' => 'string',
                'description' => 'A brief description of the webpage content, important for search engine listings',
                'required' => true,
            ],
            'keywords' => [
                'type' => 'array',
                'description' => 'A list of keywords relevant to the webpage content',
                'items' => [
                    'type' => 'string',
                ],
            ],
        ],
        'required' => ['title', 'description'],
    ]),
);

$threadFactory = new ThreadFactory($handler);
$thread = $threadFactory->createThread($expert);
$thread->addContext('content', json_encode([
    'id' => '2526a494-077b-4d08-bab6-5eb2835687a2',
    'title' => 'Drop Big Beats',
    'route' => '/blog/drop-big-beats',
    'blocks' => [
        [
            'type' => 'text',
            'settings' => [],
            'title' => 'Drop big Beats',
            'description' => '<p>Her finger on the pulse of dance and electronic music. Usually she is not listening to the music filled up with crazy beats. But today she shared her advice for ambitious DJs and electronic musicans. Her name is Charlotte Merana and she is the general Manager of the big beats of International Talents.</p>',
            'image' => [
                'id' => null
            ],
        ],
        [
            'type' => 'quote',
            'settings' => [],
            'quote' => "I'm so excited about what will come next - where will the trend blaze the trail.",
            'quoteReference' => 'Charlotte Merena',
        ],
        [
            'type' => 'text',
            'settings' => [],
            'title' => '',
            'description' => '<p>Charlotte explained, she never predicted it would get this big. In her thoughts and visions she hoped for it. But if she shared her dreams 30 years earlier, the people would laugh about her. Today you can\'t believe it. Kids loving this music. Not all of them are quite kids but for example a booking agency from Berlin in Germany signed a 14-year-old  DJ. It\'s really exciting for them, but also for me. She always believed in this subculture, but never predicted that it would get this big.</p><h3>Charlotte also shared her advice for younger artist who wants to get the attention of the people.</h3><p>She told us to beginn with your friends first. Do what you know, what you already learned. Show the people what you can do and win them. Bring the people to support you. Start throwing your party and grow up a network of people who like what you do and are excited about what you do. Don\'t make a fanbpage, and put your sounds on Soundcloud or do a crazy Photoshooing.</p>',
            'image' => [
                'id' => null
            ],
        ],
        [
            'type' => 'similar-articles',
            'settings' => [],
            'title' => '',
        ],
    ],
]));
$response = $thread->run();

echo $response->getMessage()->content;
