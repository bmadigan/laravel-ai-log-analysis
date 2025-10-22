<?php

namespace App\Actions;

use App\Models\LogEntry;
use App\Models\LogVector;
use Overpass\Facades\Overpass;

class LogVectorizer
{
    /**
     * Generate and store vector embedding for a log entry.
     */
    public function embed(LogEntry $entry): void
    {
        try {
            $embedding = Overpass::call('vectorize', ['text' => $entry->raw]);

            LogVector::create([
                'log_entry_id' => $entry->id,
                'embedding' => $embedding,
            ]);
        } catch (\Exception $e) {
            // Log the error but don't fail the job
            logger()->error('Failed to vectorize log entry', [
                'log_entry_id' => $entry->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
