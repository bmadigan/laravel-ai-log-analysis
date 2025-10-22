<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\LogEntry;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Resource;

/**
 * MCP Resource for exposing log entries and their analysis results.
 *
 * Provides access to recent log entries with associated incidents,
 * allowing AI assistants to review analysis outcomes.
 */
class LogEntriesResource extends Resource
{
    /**
     * The resource's description.
     */
    protected string $description = 'Access recent log entries with their AI analysis results, including severity levels and incident summaries.';

    /**
     * The resource's URI.
     */
    protected string $uri = 'file://logs/entries';

    /**
     * The resource's MIME type.
     */
    protected string $mimeType = 'application/json';

    /**
     * Handle the resource request.
     */
    public function handle(Request $request): Response
    {
        // Get the most recent 50 log entries with their incidents
        $entries = LogEntry::with('incidents')
            ->latest('created_at')
            ->limit(50)
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'raw' => $entry->raw,
                    'created_at' => $entry->created_at->toIso8601String(),
                    'incidents' => $entry->incidents->map(function ($incident) {
                        return [
                            'id' => $incident->id,
                            'severity' => $incident->severity,
                            'summary' => $incident->summary,
                            'created_at' => $incident->created_at->toIso8601String(),
                        ];
                    }),
                ];
            });

        if ($entries->isEmpty()) {
            return Response::text('No log entries found yet.');
        }

        return Response::json([
            'total' => $entries->count(),
            'entries' => $entries,
        ]);
    }
}
