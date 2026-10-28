<?php

namespace Tests\Unit;

use App\Services\Agreements\TfidfRetriever;
use PHPUnit\Framework\TestCase;

class TfidfRetrieverTest extends TestCase
{
    public function test_it_ranks_the_passage_with_the_strongest_term_overlap_first(): void
    {
        $passages = [
            [
                'agreement_id' => 1,
                'agreement' => 'Harbor Desk — MSA',
                'page_label' => '§ 6 · Liability',
                'snippet' => 'Each party limits its aggregate liability for indirect damages.',
            ],
            [
                'agreement_id' => 2,
                'agreement' => 'Harbor Desk — SLA',
                'page_label' => '§ 1 · Availability',
                'snippet' => 'The hosted service is available during each calendar month.',
            ],
        ];

        $results = (new TfidfRetriever)->search('aggregate liability limit', $passages);

        $this->assertSame('§ 6 · Liability', $results[0]['page_label']);
        $this->assertGreaterThan(0, $results[0]['score']);
        $this->assertCount(1, $results);
    }

    public function test_it_returns_no_passages_for_unmatched_query_terms(): void
    {
        $passages = [[
            'agreement_id' => 1,
            'agreement' => 'Harbor Desk — MSA',
            'page_label' => '§ 1 · Services',
            'snippet' => 'Services are described in a statement of work.',
        ]];

        $this->assertSame([], (new TfidfRetriever)->search('quantum spaceship', $passages));
    }
}
