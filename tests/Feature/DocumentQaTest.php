<?php

use App\Models\DocChunk;
use App\Models\LibraryDoc;
use App\Models\QaTurn;
use App\Services\Llm\FakeLlmClient;
use App\Services\Llm\LlmClient;
use App\Services\Retrieval\TfidfRetriever;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    config(['services.gemini.key' => null]);
    $this->seed();
    app()->instance(LlmClient::class, new FakeLlmClient);
});

it('renders the seeded document library and three example questions', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Welcome')
            ->has('documents', 6)
            ->has('examples', 3)
            ->where('llmMode', 'demo'));
});

it('retrieves the refunds manual for a refund question using TF-IDF', function (): void {
    $results = app(TfidfRetriever::class)->retrieve('How do I issue a refund?');

    expect($results)->not->toBeEmpty()
        ->and($results->first()->libraryDoc->filename)->toBe('refunds-manual.pdf')
        ->and($results->count())->toBeLessThanOrEqual(5);
});

it('answers with the fake LLM and cites the refunds filename', function (): void {
    $this->post(route('ask'), ['question' => 'How do I issue a refund?'])
        ->assertRedirect(route('home'))
        ->assertSessionHas('qaResult', fn (array $result): bool => str_contains($result['answer'], 'refunds-manual.pdf')
            && collect($result['citations'])->contains('filename', 'refunds-manual.pdf'));

    $turn = QaTurn::query()->firstOrFail();
    expect($turn->retrieved_chunk_ids)->not->toBeEmpty()
        ->and($turn->answer)->toContain('Sources: refunds-manual.pdf');
});

it('rebuilds overlapping chunks with the docs:ingest command', function (): void {
    $document = LibraryDoc::query()->create([
        'title' => 'Long test manual',
        'filename' => 'long-test-manual.pdf',
        'body' => str_repeat('x', 1700),
    ]);

    $this->artisan('docs:ingest')->assertSuccessful();

    $chunks = DocChunk::query()->where('library_doc_id', $document->id)->orderBy('position')->get();
    expect($chunks)->toHaveCount(4)
        ->and(mb_strlen($chunks->first()->body))->toBe(500)
        ->and(mb_substr($chunks[0]->body, -50))->toBe(mb_substr($chunks[1]->body, 0, 50))
        ->and($chunks->pluck('position')->all())->toBe([0, 1, 2, 3]);
});

it('returns a graceful retrieval-only answer if the configured model fails', function (): void {
    app()->instance(LlmClient::class, new class implements LlmClient
    {
        public function answer(string $question, array $context): string
        {
            throw new RuntimeException('Simulated provider outage');
        }
    });

    $this->post(route('ask'), ['question' => 'How do I issue a refund?'])
        ->assertRedirect(route('home'))
        ->assertSessionHas('qaResult.usedFallback', true)
        ->assertSessionHas('qaResult', fn (array $result): bool => str_contains($result['answer'], 'refunds-manual.pdf'));
});
