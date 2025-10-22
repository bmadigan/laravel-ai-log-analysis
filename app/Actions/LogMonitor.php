<?php

namespace App\Actions;

use App\Jobs\AnalyzeLogJob;
use App\Models\LogEntry;

class LogMonitor
{
    /**
     * Handle a new log line by creating a log entry and dispatching analysis.
     */
    public function handleNewLine(string $line): void
    {
        $entry = LogEntry::create(['raw' => $line]);

        dispatch(new AnalyzeLogJob($entry->id));
    }
}
