<?php

namespace App\Services\Retrieval;

use App\Models\DocChunk;
use Illuminate\Support\Collection;

class TfidfRetriever
{
    /** @return Collection<int, DocChunk> */
    public function retrieve(string $question, int $limit = 5, ?int $libraryDocId = null): Collection
    {
        $chunks = DocChunk::query()
            ->with('libraryDoc')
            ->when($libraryDocId !== null, fn ($query) => $query->where('library_doc_id', $libraryDocId))
            ->get();
        $queryTerms = $this->terms($question);

        if ($chunks->isEmpty() || $queryTerms === []) {
            return collect();
        }

        $documentTerms = $chunks->map(fn (DocChunk $chunk): array => $this->terms($chunk->body));
        $documentFrequency = [];

        foreach ($documentTerms as $terms) {
            foreach (array_unique($terms) as $term) {
                $documentFrequency[$term] = ($documentFrequency[$term] ?? 0) + 1;
            }
        }

        $totalDocuments = max(1, $chunks->count());
        $queryVector = $this->vector($queryTerms, $documentFrequency, $totalDocuments);
        $queryNorm = $this->norm($queryVector);

        return $chunks
            ->values()
            ->map(function (DocChunk $chunk, int $index) use ($documentTerms, $documentFrequency, $totalDocuments, $queryVector, $queryNorm): array {
                $vector = $this->vector($documentTerms[$index], $documentFrequency, $totalDocuments);
                $denominator = $queryNorm * $this->norm($vector);
                $score = $denominator === 0.0 ? 0.0 : array_sum(array_map(
                    fn (string $term): float => ($queryVector[$term] ?? 0.0) * ($vector[$term] ?? 0.0),
                    array_keys($queryVector),
                )) / $denominator;

                return ['chunk' => $chunk, 'score' => $score];
            })
            ->filter(fn (array $match): bool => $match['score'] > 0)
            ->sort(function (array $left, array $right): int {
                $scoreOrder = $right['score'] <=> $left['score'];

                return $scoreOrder !== 0 ? $scoreOrder : $left['chunk']->id <=> $right['chunk']->id;
            })
            ->take(max(0, $limit))
            ->map(fn (array $match): DocChunk => $match['chunk'])
            ->values();
    }

    /** @return list<string> */
    private function terms(string $text): array
    {
        preg_match_all('/[\p{L}\p{N}]{2,}/u', mb_strtolower($text), $matches);

        return array_values(array_filter($matches[0], fn (string $term): bool => ! in_array($term, [
            'about', 'after', 'also', 'and', 'are', 'can', 'does', 'for', 'from', 'have', 'how',
            'into', 'its', 'our', 'please', 'that', 'the', 'their', 'them', 'then', 'this', 'what',
            'when', 'where', 'which', 'with', 'you', 'your',
        ], true)));
    }

    /**
     * @param  list<string>  $terms
     * @param  array<string, int>  $documentFrequency
     * @return array<string, float>
     */
    private function vector(array $terms, array $documentFrequency, int $totalDocuments): array
    {
        if ($terms === []) {
            return [];
        }

        $frequencies = array_count_values($terms);
        $vector = [];

        foreach ($frequencies as $term => $frequency) {
            $inverseFrequency = log(1 + ($totalDocuments / (1 + ($documentFrequency[$term] ?? 0))));
            $vector[$term] = (1 + log($frequency)) * $inverseFrequency;
        }

        return $vector;
    }

    /** @param array<string, float> $vector */
    private function norm(array $vector): float
    {
        return sqrt(array_sum(array_map(fn (float $weight): float => $weight ** 2, $vector)));
    }
}
