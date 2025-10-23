<?php

namespace App\Actions;

use App\Models\LogEntry;
use App\Models\Severity;
use Prism\Prism\Prism;
use Prism\Prism\Schema\EnumSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

/**
 * Analyzes log entries using AI to detect issues and assess severity.
 *
 * This action uses Prism to send log entries to LLMs (Claude, GPT, etc.)
 * for intelligent analysis. It retrieves similar past entries as context
 * to improve analysis accuracy.
 */
class LogAnalyzer
{
    /**
     * Analyze a log entry using AI and return structured insights.
     *
     * @param  LogEntry  $entry  The log entry to analyze
     * @return array{severity: string, summary: string} Analysis results
     */
    public function analyze(LogEntry $entry): array
    {
        try {
            logger()->debug('LogAnalyzer.analyze invoked', ['file' => __FILE__, 'line' => __LINE__]);
            $context = $this->retrieveSimilar($entry->id);

            $prompt = "Analyze this Laravel log entry and provide severity (low, medium, high, critical) and a brief summary.

Log Entry: {$entry->raw}

Similar Past Entries:
{$context}";

            $schema = new ObjectSchema(
                'log_analysis',
                'Analysis of a log entry with severity and summary',
                [
                    new EnumSchema(
                        'severity',
                        'The severity level of the log entry',
                        [
                            Severity::Low->value,
                            Severity::Medium->value,
                            Severity::High->value,
                            Severity::Critical->value,
                        ]
                    ),
                    new StringSchema('summary', 'Brief summary of the issue'),
                ],
                ['severity', 'summary']
            );

            $builder = Prism::structured()
                ->using(
                    config('prism.log_analysis.provider'),
                    config('prism.log_analysis.model')
                )
                ->withSchema($schema)
                ->withMaxTokens((int) config('prism.log_analysis.max_tokens'))
                ->withPrompt($prompt);

            $configuredTemp = config('prism.log_analysis.temperature');
            if ($configuredTemp !== null && $configuredTemp !== '') {
                $builder = $builder->usingTemperature((float) $configuredTemp);
            }

            $response = $builder->asStructured();

            $result = $response->structured;
            $severity = Severity::fromString($result['severity'] ?? null)->value;
            $summary = isset($result['summary']) && is_string($result['summary'])
                ? $result['summary']
                : 'Log analysis completed';

            return [
                'severity' => $severity,
                'summary' => $summary,
            ];
        } catch (\Exception $e) {
            logger()->error('Failed to analyze log entry', [
                'log_entry_id' => $entry->id,
                'error' => $e->getMessage(),
            ]);

            // Return default values on error
            return [
                'severity' => Severity::Medium->value,
                'summary' => 'Analysis failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve similar log entries based on vector similarity.
     *
     * Currently uses a placeholder implementation that finds entries
     * with matching log levels. Will be enhanced with actual vector
     * similarity search using sqlite-vec.
     *
     * @param  int  $logId  The ID of the current log entry
     * @return string Formatted string of similar log entries
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
