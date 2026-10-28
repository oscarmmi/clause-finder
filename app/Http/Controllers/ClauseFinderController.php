<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Services\Agreements\ClauseFinder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClauseFinderController
{
    public function index(): Response
    {
        $agreements = Agreement::query()->orderBy('id')->get();

        return Inertia::render('Welcome', [
            'agreements' => $agreements->map(fn (Agreement $agreement): array => [
                'id' => $agreement->id,
                'title' => $agreement->title,
                'kind' => $this->agreementKind($agreement->title),
                'description' => $this->agreementDescription($agreement->title),
            ]),
            'llmAvailable' => filled(config('services.gemini.key')),
        ]);
    }

    public function search(Request $request, ClauseFinder $finder): JsonResponse
    {
        $validated = $request->validate([
            'query' => ['required', 'string', 'min:2', 'max:200'],
            'agreement_id' => ['nullable', 'integer', 'exists:agreements,id'],
        ]);

        return response()->json($finder->search(
            $validated['query'],
            isset($validated['agreement_id']) ? (int) $validated['agreement_id'] : null,
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'min:100', 'max:50000'],
        ]);

        $agreement = Agreement::query()->create($validated);

        return response()->json([
            'agreement' => [
                'id' => $agreement->id,
                'title' => $agreement->title,
                'kind' => 'TXT',
                'description' => 'Pasted text · saved in this local demo',
            ],
        ], 201);
    }

    private function agreementKind(string $title): string
    {
        return match (true) {
            str_contains($title, 'Master Services') => 'MSA',
            str_contains($title, 'Non-Disclosure') => 'NDA',
            str_contains($title, 'Service Level') => 'SLA',
            default => 'DOC',
        };
    }

    private function agreementDescription(string $title): string
    {
        return match ($this->agreementKind($title)) {
            'MSA' => 'Services, fees, ownership & risk allocation',
            'NDA' => 'Confidential information & permitted use',
            'SLA' => 'Availability, support & service credits',
            default => 'Sample agreement',
        };
    }
}
