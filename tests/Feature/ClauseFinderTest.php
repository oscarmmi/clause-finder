<?php

namespace Tests\Feature;

use App\Models\Agreement;
use App\Services\Llm\FakeLlmClient;
use App\Services\Llm\LlmClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ClauseFinderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        config(['services.gemini.key' => null]);
    }

    public function test_limitation_of_liability_search_returns_a_cited_msa_passage(): void
    {
        $msa = Agreement::query()->where('title', 'like', '%Master Services Agreement')->firstOrFail();

        $response = $this->postJson(route('search'), [
            'query' => 'limitation of liability',
        ]);

        $response->assertOk()
            ->assertJsonPath('answer_mode', 'Local fallback')
            ->assertJsonPath('citations.0.agreement', $msa->title);

        $this->assertDatabaseHas('clause_hits', [
            'agreement_id' => $msa->id,
            'query' => 'limitation of liability',
            'page_label' => '§ 6 · Limitation of liability',
        ]);

        $this->assertStringContainsString('aggregate liability', $response->json('citations.0.snippet'));
        $this->assertGreaterThan(0, $response->json('citations.0.score'));
    }

    public function test_guest_can_search_without_authentication(): void
    {
        $this->postJson(route('search'), ['query' => 'uptime commitment'])
            ->assertOk()
            ->assertJsonStructure(['answer', 'citations', 'answer_mode']);
    }

    public function test_guest_can_paste_an_agreement_and_search_its_clauses(): void
    {
        $response = $this->postJson(route('agreements.store'), [
            'title' => 'Sample Vendor Terms',
            'body' => "1. Termination\n\nEither party may terminate this agreement for a material breach if the breach remains uncured for thirty days after written notice. On termination, the customer will pay all fees for services already delivered.",
        ]);

        $response->assertCreated()
            ->assertJsonPath('agreement.title', 'Sample Vendor Terms');

        $this->postJson(route('search'), [
            'query' => 'termination for breach',
            'agreement_id' => $response->json('agreement.id'),
        ])
            ->assertOk()
            ->assertJsonPath('citations.0.agreement', 'Sample Vendor Terms')
            ->assertJsonPath('citations.0.page_label', '§ 1 · Termination');
    }

    public function test_fake_llm_is_bound_when_gemini_is_not_configured(): void
    {
        $this->assertInstanceOf(FakeLlmClient::class, app(LlmClient::class));
    }

    public function test_llm_failure_falls_back_to_a_cited_response_instead_of_a_server_error(): void
    {
        $this->app->instance(LlmClient::class, new class implements LlmClient
        {
            public function rewrite(string $query, array $citations): string
            {
                throw new RuntimeException('Simulated provider outage.');
            }
        });

        $response = $this->postJson(route('search'), ['query' => 'termination rights']);

        $response->assertOk()
            ->assertJsonPath('answer_mode', 'Local fallback')
            ->assertJsonStructure(['answer', 'citations']);
    }

    public function test_home_page_is_available_to_guests(): void
    {
        $this->get(route('home'))->assertOk();
    }
}
