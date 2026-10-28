<?php

namespace App\Services\Documents;

use App\Models\LibraryDoc;
use Illuminate\Support\Facades\DB;

class DocumentIngestor
{
    public function ingestAll(int $chunkSize = 500, int $overlap = 50): int
    {
        $count = 0;

        LibraryDoc::query()->each(function (LibraryDoc $document) use (&$count, $chunkSize, $overlap): void {
            $count += $this->ingest($document, $chunkSize, $overlap);
        });

        return $count;
    }

    public function ingest(LibraryDoc $document, int $chunkSize = 500, int $overlap = 50): int
    {
        if ($chunkSize < 1 || $overlap < 0 || $overlap >= $chunkSize) {
            throw new \InvalidArgumentException('Overlap must be non-negative and smaller than the chunk size.');
        }

        return DB::transaction(function () use ($document, $chunkSize, $overlap): int {
            $document->chunks()->delete();
            $body = trim(preg_replace('/\s+/u', ' ', $document->body) ?? $document->body);
            $chunks = [];
            $length = mb_strlen($body);
            $step = $chunkSize - $overlap;

            for ($start = 0; $start < $length; $start += $step) {
                $text = trim(mb_substr($body, $start, $chunkSize));

                if ($text !== '') {
                    $chunks[] = ['body' => $text, 'position' => count($chunks)];
                }

                if ($start + $chunkSize >= $length) {
                    break;
                }
            }

            if ($chunks !== []) {
                $document->chunks()->createMany($chunks);
            }

            return count($chunks);
        });
    }
}
