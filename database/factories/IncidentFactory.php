<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Incident>
 */
class IncidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $severities = ['low', 'medium', 'high', 'critical'];
        $severity = fake()->randomElement($severities);

        $summaries = [
            'low' => fake()->randomElement([
                'Minor performance degradation detected',
                'Non-critical cache miss observed',
                'Informational log entry generated',
            ]),
            'medium' => fake()->randomElement([
                'Slow database query requires optimization',
                'Increased memory usage detected',
                'API rate limit approaching threshold',
            ]),
            'high' => fake()->randomElement([
                'Database connection timeout occurred',
                'External service unavailable',
                'Multiple failed authentication attempts',
            ]),
            'critical' => fake()->randomElement([
                'Application crash detected',
                'Database connection pool exhausted',
                'Critical system resource shortage',
            ]),
        ];

        return [
            'log_entry_id' => \App\Models\LogEntry::factory(),
            'severity' => $severity,
            'summary' => $summaries[$severity],
        ];
    }
}
