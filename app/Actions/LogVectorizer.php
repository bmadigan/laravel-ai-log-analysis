<?php

namespace App\Actions;

use App\Models\LogEntry;
use App\Models\LogVector;
use Overpass\Facades\Overpass;

/**
 * Generates vector embeddings for log entries using ML models.
 *
 * This action uses the Overpass Python bridge to call sentence-transformers
 * and generate 384-dimensional embeddings for semantic similarity search.
 */
class LogVectorizer
{
    /**
     * Generate and store vector embedding for a log entry.
     *
     * @param  LogEntry  $entry  The log entry to vectorize
     */
    public function embed(LogEntry $entry): void
    {
        try {
            $embedding = Overpass::call('vectorize', ['text' => $entry->raw]);
            // Validate embedding structure (expect array of 384 floats)
            if (! is_array($embedding)) {
                throw new \RuntimeException('Embedding is not an array');
            }

            // Basic validation for numeric values
            $count = count($embedding);
            $isNumeric = array_reduce($embedding, function ($carry, $v) {
                return $carry && (is_int($v) || is_float($v));
            }, true);

            if ($count !== 384 || ! $isNumeric) {
                throw new \RuntimeException("Invalid embedding: count={$count} numeric=".($isNumeric ? 'yes' : 'no'));
            }

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
