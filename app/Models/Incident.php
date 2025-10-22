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
    ];

    /**
     * Get the log entry that owns this incident.
     */
    public function logEntry(): BelongsTo
    {
        return $this->belongsTo(LogEntry::class);
    }
}
