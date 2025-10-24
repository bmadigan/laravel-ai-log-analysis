<?php

namespace App\Actions;

use App\Jobs\AnalyzeLogJob;
use App\Models\LogEntry;
use Illuminate\Support\Facades\Log;

/**
 * Generates random test log errors for testing the log analysis system.
 *
 * This action creates 5-10 random log entries with varying severity levels
 * and dispatches analysis jobs for each entry.
 */
class TestErrorGenerator
{
    /**
     * Collection of test error templates by severity level.
     *
     * @var array<string, array<string>>
     */
    protected array $errorTemplates = [
        'critical' => [
            'CRITICAL: Disk space critically low - only 2% remaining on /var/log',
            'CRITICAL: Database connection pool exhausted - max connections reached',
            'CRITICAL: Memory limit exceeded - application crashed',
            'CRITICAL: SSL certificate expired - secure connections failing',
        ],
        'error' => [
            'ERROR: Failed to connect to Redis server at 127.0.0.1:6379',
            'ERROR: Query execution failed - syntax error in SQL statement',
            'ERROR: File upload failed - permission denied on storage directory',
            'ERROR: API rate limit exceeded for endpoint /api/users',
            'ERROR: Email delivery failed - SMTP authentication error',
        ],
        'warning' => [
            'WARNING: Slow query detected - execution time 5.2 seconds',
            'WARNING: Cache miss rate above 80% threshold',
            'WARNING: Session timeout increased to 2 hours',
            'WARNING: Deprecated function usage - str_random() will be removed in Laravel 13',
        ],
        'info' => [
            'INFO: Scheduled job completed successfully - ProcessReports',
            'INFO: User authentication successful for user_id: 1234',
            'INFO: Cache cleared successfully',
        ],
        'debug' => [
            'DEBUG: Route /api/logs matched middleware group',
            'DEBUG: Database query executed in 45ms',
            'DEBUG: Request payload validated successfully',
        ],
    ];

    /**
     * Generate random test errors and dispatch analysis jobs.
     *
     * @return int Number of errors generated
     */
    public function generate(): int
    {
        $count = rand(5, 10);
        $generatedCount = 0;

        for ($i = 0; $i < $count; $i++) {
            $severity = $this->getRandomSeverity();
            $message = $this->getRandomMessage($severity);

            // Write to Laravel log using appropriate method
            $this->writeToLog($severity, $message);

            // Create log entry directly (since we're generating test data)
            $entry = LogEntry::create([
                'raw' => $this->formatLogEntry($severity, $message),
            ]);

            // Dispatch analysis job
            dispatch(new AnalyzeLogJob($entry->id));

            $generatedCount++;
        }

        return $generatedCount;
    }

    /**
     * Get a random severity level with weighted distribution.
     */
    protected function getRandomSeverity(): string
    {
        // Weighted random selection - more errors/warnings, fewer critical
        $weights = [
            'critical' => 10,  // 10% chance
            'error' => 40,     // 40% chance
            'warning' => 30,   // 30% chance
            'info' => 15,      // 15% chance
            'debug' => 5,      // 5% chance
        ];

        $random = rand(1, 100);
        $cumulative = 0;

        foreach ($weights as $severity => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return $severity;
            }
        }

        return 'error'; // Fallback
    }

    /**
     * Get a random error message for the given severity.
     */
    protected function getRandomMessage(string $severity): string
    {
        $templates = $this->errorTemplates[$severity] ?? $this->errorTemplates['error'];
        $randomIndex = array_rand($templates);

        return $templates[$randomIndex];
    }

    /**
     * Write the log entry using the appropriate Log method.
     */
    protected function writeToLog(string $severity, string $message): void
    {
        match ($severity) {
            'critical' => Log::critical($message),
            'error' => Log::error($message),
            'warning' => Log::warning($message),
            'info' => Log::info($message),
            'debug' => Log::debug($message),
            default => Log::error($message),
        };
    }

    /**
     * Format log entry to match Laravel's log format.
     */
    protected function formatLogEntry(string $severity, string $message): string
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $level = strtoupper($severity);

        return "[{$timestamp}] local.{$level}: {$message}";
    }
}
