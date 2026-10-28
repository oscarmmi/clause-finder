<?php

namespace App\Services\Agreements;

class TfidfRetriever
{
    /** @var list<string> */
    private const STOP_WORDS = [
        'a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from', 'if', 'in',
        'is', 'it', 'of', 'on', 'or', 'that', 'the', 'their', 'this', 'to', 'under',
        'what', 'when', 'where', 'which', 'who', 'with',
    ];

    /**
     * Score passages against a query using TF-IDF-weighted cosine similarity.
     *
     * @param  list<array{agreement_id: int, agreement: string, page_label: string, snippet: string}>  $passages
     * @return list<array{agreement_id: int, agreement: string, page_label: string, snippet: string, score: float}>
     */
    public function search(string $query, array $passages, int $limit = 6): array
    {
        if ($passages === []) {
            return [];
        }

        $queryTerms = $this->terms($query);

        if ($queryTerms === []) {
            return [];
        }

        $documentTerms = array_map(fn (array $passage): array => $this->terms($passage['snippet']), $passages);
        $documentFrequency = [];

        foreach ($documentTerms as $terms) {
            foreach (array_unique($terms) as $term) {
                $documentFrequency[$term] = ($documentFrequency[$term] ?? 0) + 1;
            }
        }

        $documentCount = count($passages);
        $queryVector = $this->vector($queryTerms, $documentFrequency, $documentCount);

        if ($queryVector === []) {
            return [];
        }

        $ranked = [];

        foreach ($passages as $index => $passage) {
            $documentVector = $this->vector($documentTerms[$index], $documentFrequency, $documentCount);
            $score = $this->cosineSimilarity($queryVector, $documentVector);

            if ($score > 0) {
                $ranked[] = [...$passage, 'score' => $score];
            }
        }

        usort($ranked, fn (array $left, array $right): int => $right['score'] <=> $left['score']);

        return array_slice($ranked, 0, $limit);
    }

    /** @return list<string> */
    private function terms(string $text): array
    {
        $terms = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_filter(
            $terms,
            fn (string $term): bool => mb_strlen($term) > 1 && ! in_array($term, self::STOP_WORDS, true),
        ));
    }

    /**
     * @param  list<string>  $terms
     * @param  array<string, int>  $documentFrequency
     * @return array<string, float>
     */
    private function vector(array $terms, array $documentFrequency, int $documentCount): array
    {
        $counts = array_count_values($terms);
        $vector = [];

        foreach ($counts as $term => $frequency) {
            if (! isset($documentFrequency[$term])) {
                continue;
            }

            $inverseDocumentFrequency = log(1 + $documentCount / (1 + $documentFrequency[$term])) + 1;
            $vector[$term] = $frequency * $inverseDocumentFrequency;
        }

        return $vector;
    }

    /**
     * @param  array<string, float>  $left
     * @param  array<string, float>  $right
     */
    private function cosineSimilarity(array $left, array $right): float
    {
        $dotProduct = 0.0;

        foreach ($left as $term => $weight) {
            $dotProduct += $weight * ($right[$term] ?? 0.0);
        }

        $leftMagnitude = sqrt(array_sum(array_map(fn (float $weight): float => $weight ** 2, $left)));
        $rightMagnitude = sqrt(array_sum(array_map(fn (float $weight): float => $weight ** 2, $right)));

        if ($leftMagnitude === 0.0 || $rightMagnitude === 0.0) {
            return 0.0;
        }

        return $dotProduct / ($leftMagnitude * $rightMagnitude);
    }
}
