<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Actions\LogMonitor;
use Illuminate\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\File;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;

/**
 * MCP Tool for monitoring and processing log file entries.
 *
 * Reads the last N lines from the Laravel log file and processes them
 * through the log analysis pipeline for AI-powered incident detection.
 */
class MonitorLogsTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = 'Monitor Laravel log file for new entries. Reads the last N lines from the log file and processes them for AI analysis. Returns the processed log entries.';

    /**
     * Get the tool's input schema.
     *
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'lines' => $schema->integer()
                ->description('Number of recent log lines to monitor and process.')
                ->default(10)
                ->optional(),
        ];
    }

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $lines = $request->get('lines', 10);

        if ($lines <= 0 || $lines > 100) {
            return Response::error('The "lines" argument must be between 1 and 100.');
        }

        $logFile = storage_path('logs/laravel.log');

        if (! File::exists($logFile)) {
            return Response::error("Log file not found at {$logFile}");
        }

        // Read the last N lines from the log file
        $logLines = $this->readLastLines($logFile, $lines);

        if (empty($logLines)) {
            return Response::text('No log entries found.');
        }

        // Process each log line through LogMonitor
        $monitor = new LogMonitor;
        $processedCount = 0;

        foreach ($logLines as $line) {
            if (! empty(trim($line))) {
                $monitor->handleNewLine($line);
                $processedCount++;
            }
        }

        return Response::text(
            "Successfully processed {$processedCount} log entries.\n\n".
            'The entries have been queued for AI analysis. '.
            'Check the log_entries resource for results.'
        );
    }

    /**
     * Read the last N lines from a file.
     *
     * @return array<int, string>
     */
    protected function readLastLines(string $filePath, int $lines): array
    {
        $file = new \SplFileObject($filePath, 'r');
        $file->seek(PHP_INT_MAX);
        $lastLine = $file->key();

        $startLine = max(0, $lastLine - $lines);
        $file->seek($startLine);

        $result = [];
        while (! $file->eof() && count($result) < $lines) {
            $line = $file->fgets();
            if ($line !== false) {
                $result[] = rtrim($line);
            }
        }

        return $result;
    }
}
