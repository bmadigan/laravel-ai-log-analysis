<?php

namespace App\Console\Commands;

use App\Actions\LogMonitor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ProcessLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:process {--lines=50 : Number of recent log lines to process (1-1000)} {--file= : Log file path (defaults to storage/logs/laravel.log)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process the last N lines from storage/logs/laravel.log and queue analysis jobs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $lines = (int) $this->option('lines');
        $lines = max(1, min($lines, 1000));

        $logFile = $this->option('file') ?: storage_path('logs/laravel.log');
        if (! File::exists($logFile)) {
            $this->error("Log file not found at {$logFile}");

            return self::FAILURE;
        }

        $logLines = $this->readLastLines($logFile, $lines);
        if (empty($logLines)) {
            $this->info('No log entries found.');

            return self::SUCCESS;
        }

        $monitor = new LogMonitor;
        $processed = 0;
        foreach ($logLines as $line) {
            if (! empty(trim($line))) {
                $monitor->handleNewLine($line);
                $processed++;
            }
        }

        $this->info("Queued analysis for {$processed} log entr".($processed === 1 ? 'y' : 'ies').'.');

        return self::SUCCESS;
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
