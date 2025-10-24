<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents an incident detected by AI analysis of log entries.
 *
 * Incidents include severity levels (low, medium, high, critical)
 * and AI-generated summaries of the detected issues.
 */
class Incident extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'log_entry_id',
        'severity',
        'summary',
        'viewed_at',
    ];

    /**
     * Get the log entry that owns this incident.
     */
    public function logEntry(): BelongsTo
    {
        return $this->belongsTo(LogEntry::class);
    }

    /**
     * Scope to filter critical incidents that have not been viewed.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCriticalUnviewed($query)
    {
        return $query->where('severity', Severity::Critical->value)
            ->whereNull('viewed_at')
            ->latest('created_at');
    }

    /**
     * Mark this incident as viewed.
     */
    public function markAsViewed(): void
    {
        $this->update(['viewed_at' => now()]);
    }

    /**
     * Casts for model attributes.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }
}
