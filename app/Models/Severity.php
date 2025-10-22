<?php

namespace App\Models;

/**
 * Severity levels for incidents.
 */
enum Severity: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Critical = 'critical';

    public static function fromString(?string $value): self
    {
        return match (strtolower((string) $value)) {
            'low' => self::Low,
            'high' => self::High,
            'critical' => self::Critical,
            'medium' => self::Medium,
            default => self::Medium,
        };
    }
}
