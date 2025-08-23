<?php

declare(strict_types=1);

namespace Modules\Holocron\Quest\Services;

use Carbon\Carbon;

class TemporalAwarenessService
{
    private array $temporalPatterns = [
        // Relative day references
        '/\b(today|tonight)\b/i' => 0,
        '/\b(tomorrow|tmrw)\b/i' => 1,
        '/\b(yesterday)\b/i' => -1,
        '/\bday after tomorrow\b/i' => 2,

        // Relative week references
        '/\bnext week\b/i' => 7,
        '/\blast week\b/i' => -7,
        '/\bin (\d+) days?\b/i' => 'days_ahead',
        '/\bin a week\b/i' => 7,

        // Day of week references
        '/\bnext (monday|tuesday|wednesday|thursday|friday|saturday|sunday)\b/i' => 'next_weekday',
        '/\bthis (monday|tuesday|wednesday|thursday|friday|saturday|sunday)\b/i' => 'this_weekday',
        '/\blast (monday|tuesday|wednesday|thursday|friday|saturday|sunday)\b/i' => 'last_weekday',

        // Month references
        '/\bnext month\b/i' => 30,
        '/\blast month\b/i' => -30,
        '/\bin (\d+) months?\b/i' => 'months_ahead',

        // Specific date patterns
        '/\b(\d{1,2})\/(\d{1,2})\/(\d{2,4})\b/' => 'specific_date',
        '/\b(\d{1,2})-(\d{1,2})-(\d{2,4})\b/' => 'specific_date',
    ];

    /**
     * Parse text content and extract temporal references with suggested dates
     */
    public function parseTemporalReferences(string $content, ?Carbon $baseDate = null): array
    {
        $baseDate ??= now();
        $suggestions = [];

        foreach ($this->temporalPatterns as $pattern => $value) {
            if (preg_match($pattern, $content, $matches)) {
                $suggestedDate = $this->calculateDate($value, $matches, $baseDate);
                if ($suggestedDate) {
                    $suggestions[] = [
                        'matched_text' => $matches[0],
                        'suggested_date' => $suggestedDate,
                        'confidence' => $this->calculateConfidence($pattern, $matches[0]),
                    ];
                }
            }
        }

        return $suggestions;
    }

    /**
     * Get the most likely date from temporal references
     */
    public function extractPrimaryDate(string $content, ?Carbon $baseDate = null): ?Carbon
    {
        $suggestions = $this->parseTemporalReferences($content, $baseDate);

        if (empty($suggestions)) {
            return null;
        }

        // Sort by confidence and return the highest confidence suggestion
        usort($suggestions, fn($a, $b) => $b['confidence'] <=> $a['confidence']);

        return $suggestions[0]['suggested_date'];
    }

    /**
     * Calculate date based on temporal pattern value
     */
    private function calculateDate(mixed $value, array $matches, Carbon $baseDate): ?Carbon
    {
        return match ($value) {
            'days_ahead' => $baseDate->copy()->addDays((int)$matches[1]),
            'months_ahead' => $baseDate->copy()->addMonths((int)$matches[1]),
            'next_weekday' => $this->getNextWeekday($matches[1], $baseDate),
            'this_weekday' => $this->getThisWeekday($matches[1], $baseDate),
            'last_weekday' => $this->getLastWeekday($matches[1], $baseDate),
            'specific_date' => $this->parseSpecificDate($matches),
            default => is_int($value) ? $baseDate->copy()->addDays($value) : null,
        };
    }

    /**
     * Get next occurrence of a specific weekday
     */
    private function getNextWeekday(string $weekday, Carbon $baseDate): Carbon
    {
        return $baseDate->copy()->next($weekday);
    }

    /**
     * Get this week's occurrence of a specific weekday
     */
    private function getThisWeekday(string $weekday, Carbon $baseDate): Carbon
    {
        $target = $baseDate->copy()->startOfWeek()->next($weekday);

        // If the weekday already passed this week, get next week's occurrence
        if ($target->lt($baseDate)) {
            return $target->addWeek();
        }

        return $target;
    }

    /**
     * Get last occurrence of a specific weekday
     */
    private function getLastWeekday(string $weekday, Carbon $baseDate): Carbon
    {
        return $baseDate->copy()->previous($weekday);
    }

    /**
     * Parse specific date formats
     */
    private function parseSpecificDate(array $matches): ?Carbon
    {
        try {
            // Handle MM/DD/YYYY or MM-DD-YYYY formats
            if (count($matches) >= 4) {
                $month = (int)$matches[1];
                $day = (int)$matches[2];
                $year = (int)$matches[3];

                // Handle 2-digit years
                if ($year < 100) {
                    $year += $year < 50 ? 2000 : 1900;
                }

                return Carbon::createSafe($year, $month, $day);
            }
        } catch (\Exception) {
            return null;
        }

        return null;
    }

    /**
     * Calculate confidence score for a temporal match
     */
    private function calculateConfidence(string $pattern, string $matchedText): float
    {
        // Higher confidence for more specific patterns
        $confidence = match (true) {
            str_contains($pattern, 'specific_date') => 0.9,
            str_contains($pattern, 'weekday') => 0.8,
            str_contains($pattern, 'tomorrow|today') => 0.85,
            str_contains($pattern, 'next|last') => 0.7,
            default => 0.6,
        };

        // Boost confidence for longer, more specific matches
        if (strlen($matchedText) > 10) {
            $confidence += 0.1;
        }

        return min($confidence, 1.0);
    }
}
