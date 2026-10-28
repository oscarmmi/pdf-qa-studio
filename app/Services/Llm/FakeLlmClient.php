<?php

namespace App\Services\Llm;

class FakeLlmClient implements LlmClient
{
    /** @param list<array{filename: string, body: string}> $context */
    public function answer(string $question, array $context): string
    {
        if ($context === []) {
            return 'I could not find a relevant passage in the current library. Try asking about one of the available manuals.';
        }

        $passage = preg_replace('/\s+/u', ' ', $context[0]['body']) ?? $context[0]['body'];
        $passage = mb_substr($passage, 0, 360);

        return "From the library: {$passage}";
    }
}
