<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingGroup extends Model
{
    private const DAY_DEFINITIONS = [
        'monday' => ['short' => 'M', 'label' => 'Lunes'],
        'tuesday' => ['short' => 'T', 'label' => 'Martes'],
        'wednesday' => ['short' => 'X', 'label' => 'Miercoles'],
        'thursday' => ['short' => 'Th', 'label' => 'Jueves'],
        'friday' => ['short' => 'F', 'label' => 'Viernes'],
        'saturday' => ['short' => 'S', 'label' => 'Sabado'],
        'sunday' => ['short' => 'Su', 'label' => 'Domingo'],
    ];

    private const WEEKDAY_BLOCK = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

    protected $fillable = [
        'name',
        'schedule',
        'categories',
    ];

    protected function casts(): array
    {
        return [
            'schedule' => 'array',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function formattedSchedule(): array
    {
        $schedule = is_array($this->schedule) ? $this->schedule : [];

        return array_values(array_filter(array_map(
            fn (array $entry): string => self::formatScheduleEntry($entry),
            $schedule
        )));
    }

    /**
     * @param array<string, mixed> $entry
     */
    public static function formatScheduleEntry(array $entry): string
    {
        $days = self::orderedDays($entry['days'] ?? []);
        $from = self::formatTime((string) ($entry['from'] ?? ''));
        $to = self::formatTime((string) ($entry['to'] ?? ''));

        if ($days === [] || $from === '' || $to === '') {
            return '';
        }

        return self::formatDays($days).": {$from} a {$to}";
    }

    /**
     * @return array<string, array{short: string, label: string}>
     */
    public static function dayDefinitions(): array
    {
        return self::DAY_DEFINITIONS;
    }

    /**
     * @param mixed $days
     * @return array<int, string>
     */
    private static function orderedDays(mixed $days): array
    {
        if (!is_array($days)) {
            return [];
        }

        return array_values(array_filter(
            array_keys(self::DAY_DEFINITIONS),
            fn (string $day): bool => in_array($day, $days, true)
        ));
    }

    /**
     * @param array<int, string> $days
     */
    private static function formatDays(array $days): string
    {
        if ($days === self::WEEKDAY_BLOCK) {
            return 'M-F (Lunes a viernes)';
        }

        if (self::isContiguous($days) && count($days) > 1) {
            $firstDay = $days[0];
            $lastDay = $days[count($days) - 1];
            $first = self::DAY_DEFINITIONS[$firstDay];
            $last = self::DAY_DEFINITIONS[$lastDay];

            return "{$first['short']}-{$last['short']} ({$first['label']} a ".strtolower($last['label']).')';
        }

        $shortDays = array_map(fn (string $day): string => self::DAY_DEFINITIONS[$day]['short'], $days);
        $labels = array_map(fn (string $day): string => self::DAY_DEFINITIONS[$day]['label'], $days);

        return implode(',', $shortDays).' ('.implode(', ', $labels).')';
    }

    /**
     * @param array<int, string> $days
     */
    private static function isContiguous(array $days): bool
    {
        $dayOrder = array_flip(array_keys(self::DAY_DEFINITIONS));
        $indexes = array_map(fn (string $day): int => $dayOrder[$day], $days);

        return max($indexes) - min($indexes) + 1 === count($indexes);
    }

    private static function formatTime(string $time): string
    {
        return preg_replace('/^0(\d:\d{2})$/', '$1', $time) ?? $time;
    }
}
