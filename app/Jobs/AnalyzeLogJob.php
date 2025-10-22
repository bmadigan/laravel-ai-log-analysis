<?php

namespace App\Jobs;

use App\Actions\IncidentManager;
use App\Actions\LogAnalyzer;
use App\Actions\LogVectorizer;
use App\Models\LogEntry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AnalyzeLogJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $logEntryId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $entry = LogEntry::find($this->logEntryId);

        if (! $entry) {
            return;
        }

        // Step 1: Vectorize the log entry
        $vectorizer = new LogVectorizer;
        $vectorizer->embed($entry);

        // Step 2: Analyze the log entry with AI
        $analyzer = new LogAnalyzer;
        $analysis = $analyzer->analyze($entry);

        // Step 3: Create an incident from the analysis
        $incidentManager = new IncidentManager;
        $incidentManager->createFromAnalysis($entry, $analysis);
    }
}
