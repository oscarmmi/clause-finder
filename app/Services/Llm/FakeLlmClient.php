<?php

namespace App\Services\Llm;

class FakeLlmClient implements LlmClient
{
    public function rewrite(string $query, array $citations): string
    {
        if ($citations === []) {
            return 'No close matching passage was found in the selected sample agreements. Try different wording or search all agreements.';
        }

        $sources = count($citations) === 1 ? 'one source passage' : count($citations).' source passages';

        return "The strongest matches for “{$query}” appear in {$sources}. The excerpts below are the source text; use them to review the exact wording and surrounding context.";
    }
}
