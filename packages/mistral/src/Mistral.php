<?php

namespace ModelflowAi\Mistral;

final class Mistral
{
    private function __construct()
    {
    }

    public static function client(string $apiKey): Client
    {
        return self::factory()
            ->withApiKey($apiKey)
            ->make();
    }

    public static function factory(): Factory
    {
        return new Factory();
    }
}
