<?php

namespace App\Actions;

use App\Jobs\AnalyzeLogJob;
use App\Models\LogEntry;
use Illuminate\Support\Facades\Cache;

/**
 * Monitors and processes new log entries.
 *
 * This action is responsible for ingesting new log lines from the Laravel
 * log file, creating LogEntry records, and dispatching async analysis jobs.
 */
class LogMonitor
{
    /**
     * Handle a new log line by creating a log entry and dispatching analysis.
     *
     * @param  string  $line  The raw log line to process
     */
    public function handleNewLine(string $line): void
    {
        $hash = 'processed_log:'.sha1($line);

        // Deduplicate recently processed identical lines (5 minutes TTL)
        if (! Cache::add($hash, true, now()->addMinutes(5))) {
            return;
        }

        $entry = LogEntry::create(['raw' => $line]);

        dispatch(new AnalyzeLogJob($entry->id));
    }
}
