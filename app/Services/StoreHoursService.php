<?php

namespace App\Services;

use App\Models\StoreSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;

class StoreHoursService
{
    public const SETTINGS_KEY = 'store_hours';
    public const SLOT_MINUTES = 120;

    private const DAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    public function get(): array
    {
        if (!Schema::hasTable('store_settings')) {
            return $this->normalize([]);
        }

        $raw = StoreSetting::query()
            ->where('key', self::SETTINGS_KEY)
            ->value('value');

        $settings = is_array($raw) ? $raw : [];

        return $this->normalize($settings);
    }

    public function save(array $settings): array
    {
        $normalized = $this->normalize($settings);

        if (!Schema::hasTable('store_settings')) {
            return $normalized;
        }

        StoreSetting::query()->updateOrCreate(
            ['key' => self::SETTINGS_KEY],
            ['value' => $normalized]
        );

        return $normalized;
    }

    public function isOpenAt(Carbon $when): bool
    {
        $settings = $this->get();

        if (!($settings['is_open'] ?? true)) {
            return false;
        }

        $day = strtolower($when->englishDayOfWeek);
        $dayConfig = $settings['weekly'][$day] ?? null;
        if (!$dayConfig || !($dayConfig['enabled'] ?? false)) {
            return false;
        }

        $time = $when->format('H:i');
        return $time >= $dayConfig['open'] && $time < $dayConfig['close'];
    }

    public function nextOpeningAfter(Carbon $from): ?Carbon
    {
        $settings = $this->get();
        if (!($settings['is_open'] ?? true)) {
            return null;
        }

        for ($i = 0; $i <= 14; $i++) {
            $candidateDate = $from->copy()->addDays($i);
            $day = strtolower($candidateDate->englishDayOfWeek);
            $dayConfig = $settings['weekly'][$day] ?? null;

            if (!$dayConfig || !($dayConfig['enabled'] ?? false)) {
                continue;
            }

            $openAt = Carbon::parse($candidateDate->toDateString() . ' ' . $dayConfig['open']);
            $closeAt = Carbon::parse($candidateDate->toDateString() . ' ' . $dayConfig['close']);

            if ($i === 0) {
                if ($from->lt($openAt)) {
                    return $openAt;
                }

                if ($from->betweenIncluded($openAt, $closeAt->copy()->subMinute())) {
                    return $from->copy();
                }

                continue;
            }

            return $openAt;
        }

        return null;
    }

    public function closedMessage(Carbon $from): string
    {
        $settings = $this->get();
        if (!($settings['is_open'] ?? true)) {
            return 'Store is temporarily closed. Please try again later.';
        }

        $next = $this->nextOpeningAfter($from);
        if (!$next) {
            return 'Store is currently closed. No opening hours are configured yet.';
        }

        return 'Store is currently closed. Next opening: ' . $next->format('D, M d, Y h:i A') . '.';
    }

    public function scheduleSlotsForDate(Carbon $date, int $slotMinutes = self::SLOT_MINUTES): array
    {
        $settings = $this->get();
        if (!($settings['is_open'] ?? true)) {
            return [];
        }

        $day = strtolower($date->englishDayOfWeek);
        $dayConfig = $settings['weekly'][$day] ?? null;
        if (!$dayConfig || !($dayConfig['enabled'] ?? false)) {
            return [];
        }

        $openAt = Carbon::parse($date->toDateString() . ' ' . $dayConfig['open']);
        $closeAt = Carbon::parse($date->toDateString() . ' ' . $dayConfig['close']);

        if (!$openAt->lt($closeAt)) {
            return [];
        }

        $slots = [];
        $cursor = $openAt->copy();

        while ($cursor->lt($closeAt)) {
            $end = $cursor->copy()->addMinutes($slotMinutes);
            if ($end->gt($closeAt)) {
                $end = $closeAt->copy();
            }

            $slots[] = [
                'value' => $cursor->format('H:i') . '-' . $end->format('H:i'),
                'label' => $cursor->format('g:i A') . ' - ' . $end->format('g:i A'),
            ];

            if ($end->equalTo($cursor)) {
                break;
            }

            $cursor = $end;
        }

        return $slots;
    }

    public function dayKeys(): array
    {
        return self::DAYS;
    }

    private function normalize(array $settings): array
    {
        $weekly = [];
        $inputWeekly = $settings['weekly'] ?? [];

        foreach (self::DAYS as $day) {
            $dayConfig = $inputWeekly[$day] ?? [];
            $weekly[$day] = [
                'enabled' => (bool) ($dayConfig['enabled'] ?? in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'], true)),
                'open' => $this->normalizeTime($dayConfig['open'] ?? '09:00'),
                'close' => $this->normalizeTime($dayConfig['close'] ?? '17:00'),
            ];
        }

        return [
            'is_open' => (bool) ($settings['is_open'] ?? true),
            'weekly' => $weekly,
        ];
    }

    private function normalizeTime(string $time): string
    {
        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            return '09:00';
        }

        return $time;
    }
}
