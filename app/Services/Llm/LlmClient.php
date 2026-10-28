<?php

namespace App\Services\Llm;

interface LlmClient
{
    /** @param list<array{filename: string, body: string}> $context */
    public function answer(string $question, array $context): string;
}
