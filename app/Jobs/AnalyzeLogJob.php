<?php

namespace App\Jobs;

use App\Actions\IncidentManager;
use App\Actions\LogAnalyzer;
use App\Actions\LogVectorizer;
use App\Models\LogEntry;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Queued job for asynchronous log entry analysis.
 *
 * Orchestrates the complete log analysis workflow:
 * 1. Generates vector embedding via LogVectorizer
 * 2. Performs AI analysis via LogAnalyzer
 * 3. Creates incident record via IncidentManager
 */
class AnalyzeLogJob implements ShouldQueue
{
    use Queueable;

    /**
     * Maximum number of attempts.
     */
    public int $tries = 3;

    /**
     * Seconds to wait before retrying.
     */
    public int $backoff = 10;

    /**
     * Create a new job instance.
     *
     * @param  int  $logEntryId  The ID of the log entry to analyze
     */
    public function __construct(
        public int $logEntryId,
    ) {}

    /**
     * Execute the job.
     *
     * Processes the log entry through the complete analysis pipeline.
     * If the entry is not found, the job silently completes.
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

    // Use the default queue; keep things simple for the tutorial.
}
