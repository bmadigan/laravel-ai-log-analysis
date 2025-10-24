<?php

namespace App\Livewire;

use App\Models\Incident;
use App\Models\LogEntry;
use Livewire\Component;

/**
 * Log Analysis Dashboard component.
 *
 * Displays recent log entries and AI-detected incidents with
 * real-time refresh capability.
 */
class LogDashboard extends Component
{
    /**
     * Collection of recent log entries.
     *
     * @var array
     */
    public $logEntries = [];

    /**
     * Collection of recent incidents.
     *
     * @var array
     */
    public $incidents = [];

    /**
     * Collection of critical unviewed incidents.
     *
     * @var array
     */
    public $criticalAlerts = [];

    /**
     * Initialize the component.
     */
    public function mount(): void
    {
        $this->refreshData();
    }

    /**
     * Refresh dashboard data from database.
     */
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
                'log_preview' => $incident->logEntry ? substr($incident->logEntry->raw, 0, 100).'...' : 'N/A',
            ])
            ->toArray();

        // Load critical unviewed incidents for alerts
        $this->criticalAlerts = Incident::criticalUnviewed()
            ->with('logEntry')
            ->get()
            ->map(fn ($incident) => [
                'id' => $incident->id,
                'severity' => $incident->severity,
                'summary' => $incident->summary,
                'created_at' => $incident->created_at->diffForHumans(),
                'log_preview' => $incident->logEntry ? substr($incident->logEntry->raw, 0, 100).'...' : 'N/A',
            ])
            ->toArray();
    }

    /**
     * Get Tailwind border class based on severity.
     *
     * @param  string  $severity  The incident severity level
     * @return string Tailwind CSS border class
     */
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

    /**
     * Get Tailwind badge class based on severity.
     *
     * @param  string  $severity  The incident severity level
     * @return string Tailwind CSS badge class
     */
    public function getSeverityBadgeClass(string $severity): string
    {
        return match ($severity) {
            'critical' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            'medium' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'low' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        };
    }

    /**
     * Generate random test errors for testing the log analysis system.
     */
    public function generateTestErrors(): void
    {
        $generator = new \App\Actions\TestErrorGenerator;
        $count = $generator->generate();

        // Show success notification (Livewire 3 flash message)
        session()->flash('message', "Generated {$count} test errors. Analysis jobs dispatched.");

        // Refresh data to show new logs (incidents will appear after jobs process)
        $this->refreshData();
    }

    /**
     * Dismiss a critical alert by marking it as viewed.
     *
     * @param  int  $incidentId  The ID of the incident to dismiss
     */
    public function dismissAlert(int $incidentId): void
    {
        $incident = Incident::find($incidentId);

        if ($incident) {
            $incident->markAsViewed();
            $this->refreshData();
        }
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.log-dashboard');
    }
}
