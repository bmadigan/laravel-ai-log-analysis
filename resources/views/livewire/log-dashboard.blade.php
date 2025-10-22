<?php

use App\Models\Incident;
use App\Models\LogEntry;
use Livewire\Volt\Component;

new class extends Component
{
    public $logEntries = [];
    public $incidents = [];

    public function mount(): void
    {
        $this->refreshData();
    }

    public function refreshData(): void
    {
        // Load the latest 10 log entries
        $this->logEntries = LogEntry::latest('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($entry) => [
                'id' => $entry->id,
                'raw' => $entry->raw,
                'created_at' => $entry->created_at->diffForHumans(),
            ])
            ->toArray();

        // Load the latest 5 incidents with severity indicators
        $this->incidents = Incident::with('logEntry')
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn ($incident) => [
                'id' => $incident->id,
                'severity' => $incident->severity,
                'summary' => $incident->summary,
                'created_at' => $incident->created_at->diffForHumans(),
                'log_preview' => $incident->logEntry ? substr($incident->logEntry->raw, 0, 100) . '...' : 'N/A',
            ])
            ->toArray();
    }

    public function getSeverityBorderClass(string $severity): string
    {
        return match ($severity) {
            'critical' => 'border-red-600',
            'high' => 'border-orange-500',
            'medium' => 'border-yellow-500',
            'low' => 'border-green-500',
            default => 'border-gray-300',
        };
    }

    public function getSeverityBadgeClass(string $severity): string
    {
        return match ($severity) {
            'critical' => 'bg-red-100 text-red-800',
            'high' => 'bg-orange-100 text-orange-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'low' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function with(): array
    {
        return [
            'logEntries' => $this->logEntries,
            'incidents' => $this->incidents,
        ];
    }
}; ?>

<div class="min-h-screen bg-gray-100 dark:bg-gray-900">
    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Log Analysis Dashboard</h1>
                <button
                    wire:click="refreshData"
                    class="bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors"
                >
                    <span wire:loading.remove>Refresh Data</span>
                    <span wire:loading>Loading...</span>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Log Entries Panel -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Recent Log Entries</h2>

                    <div wire:loading class="text-center py-4">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 dark:border-blue-400"></div>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Loading logs...</p>
                    </div>

                    <div wire:loading.remove class="space-y-4">
                        @forelse ($logEntries as $entry)
                            <div class="border-l-4 border-gray-300 dark:border-gray-600 pl-4 py-2">
                                <p class="text-sm font-mono text-gray-700 dark:text-gray-300 break-all">{{ $entry['raw'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $entry['created_at'] }}</p>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400 text-center py-8">No log entries yet.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Incidents Panel -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">AI-Detected Incidents</h2>

                    <div wire:loading class="text-center py-4">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 dark:border-blue-400"></div>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Loading incidents...</p>
                    </div>

                    <div wire:loading.remove class="space-y-4">
                        @forelse ($incidents as $incident)
                            <div class="border-l-4 {{ $this->getSeverityBorderClass($incident['severity']) }} pl-4 py-2">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2 py-1 text-xs font-semibold rounded {{ $this->getSeverityBadgeClass($incident['severity']) }}">
                                        {{ strtoupper($incident['severity']) }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $incident['created_at'] }}</span>
                                </div>
                                <p class="text-sm text-gray-800 dark:text-gray-200 font-medium">{{ $incident['summary'] }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 font-mono">{{ $incident['log_preview'] }}</p>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400 text-center py-8">No incidents detected yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
