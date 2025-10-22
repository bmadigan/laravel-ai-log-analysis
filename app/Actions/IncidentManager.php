<?php

namespace App\Actions;

use App\Models\Incident;
use App\Models\LogEntry;
use App\Models\Severity;

/**
 * Manages incident creation and tracking based on log analysis.
 *
 * This action creates Incident records from AI analysis results,
 * validating severity levels and ensuring data integrity.
 */
class IncidentManager
{
    /**
     * Create an incident from log analysis results.
     *
     * @param  LogEntry  $entry  The log entry that triggered the incident
     * @param  array{severity: string, summary: string}  $analysis  AI analysis results
     */
    public function createFromAnalysis(LogEntry $entry, array $analysis): void
    {
        $severity = Severity::fromString($analysis['severity'] ?? null)->value;
        $summary = $analysis['summary'] ?? 'No summary provided.';

        // Validate severity value
        // Severity already normalized via enum

        Incident::create([
            'log_entry_id' => $entry->id,
            'severity' => $severity,
            'summary' => $summary,
        ]);
    }
}
