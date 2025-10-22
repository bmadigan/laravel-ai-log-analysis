<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Resources\LogEntriesResource;
use App\Mcp\Tools\MonitorLogsTool;
use Laravel\Mcp\Server;

/**
 * MCP Server for real-time log monitoring and analysis.
 *
 * Provides tools and resources for AI assistants to interact with
 * the log analysis system via the Model Context Protocol.
 */
class LogWatcherServer extends Server
{
    protected string $name = 'Log Watcher';

    protected string $version = '1.0.0';

    protected string $instructions = <<<'MARKDOWN'
        This MCP server provides real-time log monitoring and analysis capabilities.

        ## Available Tools
        - **monitor_logs**: Watch Laravel log file for new entries and process them with AI

        ## Available Resources
        - **log_entries**: Access recent log entries and their AI analysis results

        Use these tools to monitor application logs, detect issues, and track incidents.
    MARKDOWN;

    /**
     * @var array<int, class-string>
     */
    protected array $tools = [
        MonitorLogsTool::class,
    ];

    /**
     * @var array<int, class-string>
     */
    protected array $resources = [
        LogEntriesResource::class,
    ];

    protected function boot(): void
    {
        // Server initialization logic if needed
    }
}
