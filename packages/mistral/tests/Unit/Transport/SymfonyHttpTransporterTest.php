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

namespace ModelflowAi\Mistral\Tests\Unit\Transport;

use ModelflowAi\Mistral\Tests\DataFixtures;
use ModelflowAi\Mistral\Transport\Payload;
use ModelflowAi\Mistral\Transport\Response\ObjectResponse;
use ModelflowAi\Mistral\Transport\Response\TextResponse;
use ModelflowAi\Mistral\Transport\SymfonyHttpTransporter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class SymfonyHttpTransporterTest extends TestCase
{
    public function testRequestText(): void
    {
        $transporter = $this->createInstance(new MockResponse('Test text', ['http_code' => 200]));

        $payload = Payload::create('chat/completions', DataFixtures::CHAT_CREATE_REQUEST);

        $response = $transporter->requestText($payload);

        $this->assertInstanceOf(TextResponse::class, $response);
        $this->assertSame('Test text', $response->text);
    }

    public function testRequestObject(): void
    {
        $transporter = $this->createInstance(new MockResponse(
            (string) \json_encode(DataFixtures::CHAT_CREATE_RESPONSE),
            ['http_code' => 200],
        ));

        $payload = Payload::create('chat/completions', DataFixtures::CHAT_CREATE_REQUEST);

        $response = $transporter->requestObject($payload);

        $this->assertInstanceOf(ObjectResponse::class, $response);
        $this->assertSame(DataFixtures::CHAT_CREATE_RESPONSE, $response->data);
    }

    private function createInstance(ResponseInterface $response): SymfonyHttpTransporter
    {
        return new SymfonyHttpTransporter(new MockHttpClient($response), 'https://api.example.com');
    }
}
