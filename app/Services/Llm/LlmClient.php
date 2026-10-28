<?php

namespace App\Services\Llm;

interface LlmClient
{
    /**
     * Rewrite retrieved evidence into a concise, grounded answer.
     *
     * @param  list<array{agreement: string, page_label: string, snippet: string}>  $citations
     */
    public function rewrite(string $query, array $citations): string;
}
