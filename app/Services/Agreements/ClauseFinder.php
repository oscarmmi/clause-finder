<?php

namespace App\Services\Agreements;

use App\Models\Agreement;
use App\Models\ClauseHit;
use App\Services\Llm\FakeLlmClient;
use App\Services\Llm\LlmClient;
use Throwable;

class ClauseFinder
{
    public function __construct(
        private readonly AgreementChunker $chunker,
        private readonly TfidfRetriever $retriever,
        private readonly LlmClient $llm,
    ) {}

    /** @return array{answer: string, citations: list<array{id: int, agreement: string, page_label: string, snippet: string, score: float}>, answer_mode: string} */
    public function search(string $query, ?int $agreementId = null): array
    {
        $agreements = Agreement::query()
            ->when($agreementId !== null, fn ($builder) => $builder->whereKey($agreementId))
            ->get();

        $passages = array_values($agreements
            ->flatMap(fn (Agreement $agreement): array => $this->chunker->chunk($agreement))
            ->all());

        $ranked = $this->retriever->search($query, $passages);
        $citations = [];

        foreach ($ranked as $passage) {
            $hit = ClauseHit::query()->create([
                'agreement_id' => $passage['agreement_id'],
                'query' => $query,
                'snippet' => $passage['snippet'],
                'score' => $passage['score'],
                'page_label' => $passage['page_label'],
            ]);

            $citations[] = [
                'id' => $hit->id,
                'agreement' => $passage['agreement'],
                'page_label' => $passage['page_label'],
                'snippet' => $passage['snippet'],
                'score' => $passage['score'],
            ];
        }

        $fallback = ! config('services.gemini.key');

        try {
            $answer = $this->llm->rewrite($query, array_map(
                fn (array $citation): array => [
                    'agreement' => $citation['agreement'],
                    'page_label' => $citation['page_label'],
                    'snippet' => $citation['snippet'],
                ],
                $citations,
            ));
        } catch (Throwable $exception) {
            report($exception);
            $answer = (new FakeLlmClient)->rewrite($query, array_map(
                fn (array $citation): array => [
                    'agreement' => $citation['agreement'],
                    'page_label' => $citation['page_label'],
                    'snippet' => $citation['snippet'],
                ],
                $citations,
            ));
            $fallback = true;
        }

        return [
            'answer' => $answer,
            'citations' => $citations,
            'answer_mode' => $fallback ? 'Local fallback' : 'Gemini rewrite',
        ];
    }
}
