<?php

namespace App\Actions;

use App\Jobs\AnalyzeLogJob;
use App\Models\LogEntry;

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
        $entry = LogEntry::create(['raw' => $line]);

        dispatch(new AnalyzeLogJob($entry->id));
    }
}
