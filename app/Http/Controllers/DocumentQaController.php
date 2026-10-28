<?php

namespace App\Http\Controllers;

use App\Models\DocChunk;
use App\Models\LibraryDoc;
use App\Models\QaTurn;
use App\Services\Llm\LlmClient;
use App\Services\Retrieval\TfidfRetriever;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class DocumentQaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Welcome', [
            'documents' => LibraryDoc::query()
                ->withCount('chunks')
                ->orderBy('title')
                ->get(['id', 'title', 'filename', 'updated_at'])
                ->map(fn (LibraryDoc $document): array => [
                    'id' => $document->id,
                    'title' => $document->title,
                    'filename' => $document->filename,
                    'chunks' => $document->chunks_count,
                    'updatedAt' => $document->updated_at?->toIso8601String(),
                ]),
            'examples' => [
                'How do I issue a refund?',
                'What response time does the SLA promise?',
                'When should I escalate a critical incident?',
            ],
            'llmMode' => filled(config('services.gemini.key')) ? 'gemini' : 'demo',
        ]);
    }

    public function ask(Request $request, TfidfRetriever $retriever, LlmClient $llm): RedirectResponse
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:5', 'max:1000'],
            'documentId' => ['nullable', 'integer', 'exists:library_docs,id'],
        ]);

        $chunks = $retriever->retrieve($validated['question'], 5, $validated['documentId'] ?? null);
        $context = array_values($chunks->map(fn (DocChunk $chunk): array => [
            'filename' => $chunk->libraryDoc->filename,
            'body' => $chunk->body,
        ])->all());

        $usedFallback = false;

        try {
            $answer = $llm->answer($validated['question'], $context);
        } catch (Throwable $exception) {
            Log::warning('Document Q&A model failed; using retrieved passages.', [
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
            $usedFallback = true;
            $answer = $context === []
                ? 'I could not find a relevant passage in the current library. Try one of the example questions or ask about a different manual.'
                : 'The model is temporarily unavailable, so here are the closest passages from your library: '.preg_replace('/\s+/u', ' ', $context[0]['body']);
        }

        $citations = array_values($chunks
            ->unique('library_doc_id')
            ->take(5)
            ->map(fn (DocChunk $chunk): array => [
                'filename' => $chunk->libraryDoc->filename,
                'title' => $chunk->libraryDoc->title,
                'excerpt' => $chunk->body,
                'position' => $chunk->position + 1,
            ])
            ->all());

        if ($citations !== []) {
            $sourceFiles = array_unique(array_column($citations, 'filename'));
            $answer .= "\n\nSources: ".implode(', ', $sourceFiles);
        }

        $turn = QaTurn::query()->create([
            'question' => $validated['question'],
            'answer' => $answer,
            'retrieved_chunk_ids' => $chunks->pluck('id')->all(),
        ]);

        return to_route('home')->with('qaResult', [
            'id' => $turn->id,
            'question' => $turn->question,
            'answer' => $turn->answer,
            'citations' => $citations,
            'retrievedCount' => $chunks->count(),
            'usedFallback' => $usedFallback,
        ]);
    }
}
