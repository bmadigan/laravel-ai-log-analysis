<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LogVector>
 */
class LogVectorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a placeholder 384-dimensional embedding vector
        // (typical dimension for all-MiniLM-L6-v2 model)
        $embedding = [];
        for ($i = 0; $i < 384; $i++) {
            $embedding[] = fake()->randomFloat(6, -1, 1);
        }

        return [
            'log_entry_id' => \App\Models\LogEntry::factory(),
            'embedding' => $embedding,
        ];
    }
}
