<?php

namespace App\Actions;

use App\Models\LogEntry;
use Prism\Facades\Prism;

class LogAnalyzer
{
    /**
     * Analyze a log entry using AI and return structured insights.
     *
     * @return array{severity: string, summary: string}
     */
    public function analyze(LogEntry $entry): array
    {
        try {
            $context = $this->retrieveSimilar($entry->id);

            $prompt = "Analyze this Laravel log entry and provide severity (low, medium, high, critical) and a brief summary.

Log Entry: {$entry->raw}

Similar Past Entries:
{$context}

Return a JSON object with 'severity' and 'summary' fields.";

            $response = Prism::text()
                ->using(
                    config('prism.log_analysis.provider'),
                    config('prism.log_analysis.model')
                )
                ->withMaxTokens(config('prism.log_analysis.max_tokens'))
                ->withTemperature(config('prism.log_analysis.temperature'))
                ->prompt($prompt)
                ->generate();

            // Parse JSON response
            $result = json_decode($response, true);

            return [
                'severity' => $result['severity'] ?? 'medium',
                'summary' => $result['summary'] ?? 'Log analysis completed',
            ];
        } catch (\Exception $e) {
            logger()->error('Failed to analyze log entry', [
                'log_entry_id' => $entry->id,
                'error' => $e->getMessage(),
            ]);

            // Return default values on error
            return [
                'severity' => 'medium',
                'summary' => 'Analysis failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve similar log entries based on vector similarity.
     */
    protected function retrieveSimilar(int $logId): string
    {
        // Placeholder: Will implement proper vector similarity search later
        // For now, just return recent similar log levels
        $entry = LogEntry::find($logId);

        if (! $entry) {
            return 'No prior context found.';
        }

        // Extract log level from the raw log
        preg_match('/\.(DEBUG|INFO|NOTICE|WARNING|ERROR|CRITICAL|ALERT|EMERGENCY):/', $entry->raw, $matches);
        $level = $matches[1] ?? null;

        if ($level) {
            $similar = LogEntry::where('raw', 'like', "%{$level}:%")
                ->where('id', '!=', $logId)
                ->latest('created_at')
                ->limit(3)
                ->pluck('raw')
                ->implode("\n");

            return $similar ?: 'No similar entries found.';
        }

        return 'No prior context found.';
    }
}
