<?php

namespace App\Console\Commands;

use App\Services\Documents\DocumentIngestor;
use Illuminate\Console\Command;

class IngestDocuments extends Command
{
    protected $signature = 'docs:ingest';

    protected $description = 'Rebuild overlapping text chunks for every document in the library';

    public function handle(DocumentIngestor $ingestor): int
    {
        $count = $ingestor->ingestAll();
        $this->info("Ingested {$count} chunks across the document library.");

        return self::SUCCESS;
    }
}
