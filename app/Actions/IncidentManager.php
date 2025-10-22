<?php

namespace App\Actions;

use App\Models\Incident;
use App\Models\LogEntry;

class IncidentManager
{
    /**
     * Create an incident from log analysis results.
     *
     * @param  array{severity: string, summary: string}  $analysis
     */
    public function createFromAnalysis(LogEntry $entry, array $analysis): void
    {
        $severity = $analysis['severity'] ?? 'medium';
        $summary = $analysis['summary'] ?? 'No summary provided.';

        // Validate severity value
        $validSeverities = ['low', 'medium', 'high', 'critical'];
        if (! in_array($severity, $validSeverities)) {
            $severity = 'medium';
        }

        Incident::create([
            'log_entry_id' => $entry->id,
            'severity' => $severity,
            'summary' => $summary,
        ]);
    }
}
