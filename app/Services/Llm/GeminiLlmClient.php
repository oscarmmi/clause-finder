<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiLlmClient implements LlmClient
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'gemini-2.0-flash',
    ) {}

    public function rewrite(string $query, array $citations): string
    {
        $evidence = collect($citations)->map(fn (array $citation, int $index): string => sprintf(
            '[%d] %s, %s: %s',
            $index + 1,
            $citation['agreement'],
            $citation['page_label'],
            $citation['snippet'],
        ))->implode("\n");

        $response = Http::acceptJson()
            ->connectTimeout(4)
            ->timeout(12)
            ->withHeaders(['x-goog-api-key' => $this->apiKey])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent", [
                'systemInstruction' => [
                    'parts' => [[
                        'text' => 'Summarize contract evidence for a research aid. Use only the provided passages, do not infer missing terms, do not give legal advice, and cite sources inline using [1], [2]. If the passages do not answer the question, say so.',
                    ]],
                ],
                'contents' => [[
                    'role' => 'user',
                    'parts' => [[
                        'text' => "Question: {$query}\n\nRetrieved source passages:\n{$evidence}",
                    ]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 220,
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Gemini answer rewrite failed with HTTP '.$response->status().'.');
        }

        $answer = $response->json('candidates.0.content.parts.0.text');

        if (! is_string($answer) || trim($answer) === '') {
            throw new RuntimeException('Gemini returned an empty answer rewrite.');
        }

        return trim($answer);
    }
}
