<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LogEntry>
 */
class LogEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $levels = ['DEBUG', 'INFO', 'NOTICE', 'WARNING', 'ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'];
        $level = fake()->randomElement($levels);

        $messages = [
            'DEBUG' => 'Query executed in '.fake()->numberBetween(1, 100).'ms',
            'INFO' => 'User '.fake()->userName().' logged in successfully',
            'NOTICE' => 'Cache key "'.fake()->word().'" was set',
            'WARNING' => 'Slow query detected: SELECT * FROM users WHERE id = '.fake()->randomNumber(),
            'ERROR' => 'Database connection failed: '.fake()->sentence(),
            'CRITICAL' => 'Out of memory error in '.fake()->word().' process',
            'ALERT' => 'System load exceeds threshold: '.fake()->randomFloat(2, 5, 10),
            'EMERGENCY' => 'Application crashed: '.fake()->sentence(),
        ];

        $timestamp = fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d H:i:s');

        return [
            'raw' => "[{$timestamp}] local.{$level}: {$messages[$level]}",
        ];
    }
}
