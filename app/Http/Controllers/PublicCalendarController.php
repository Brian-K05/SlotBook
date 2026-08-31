<?php

namespace App\Http\Controllers;

use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PublicCalendarController extends Controller
{
    public function __invoke(Request $request): View
    {
        $month = $this->resolveMonth($request->query('month'));
        $gridStart = $month->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $gridEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $slots = Slot::query()
            ->with('activeBooking')
            ->where('starts_at', '>=', $gridStart->format('Y-m-d H:i:s'))
            ->where('starts_at', '<=', $gridEnd->format('Y-m-d H:i:s'))
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (Slot $slot) => $slot->starts_at->copy()->timezone((string) config('app.timezone'))->toDateString());

        $days = [];
        $cursor = $gridStart->copy();

        while ($cursor->lte($gridEnd)) {
            $key = $cursor->toDateString();
            $daySlots = $slots->get($key, collect())->map(function (Slot $slot) {
                return [
                    'id' => $slot->id,
                    'time' => $slot->starts_at->format('g:i A'),
                    'range' => $slot->starts_at->format('g:i').'–'.$slot->ends_at->format('g:i A'),
                    'open' => $slot->isOpen(),
                    'past' => $slot->starts_at->isPast(),
                ];
            })->values();

            $openCount = $daySlots->where('open', true)->count();

            $days[] = [
                'date' => $key,
                'day' => $cursor->day,
                'inMonth' => $cursor->month === $month->month,
                'isToday' => $cursor->isToday(),
                'openCount' => $openCount,
                'slotCount' => $daySlots->count(),
                'allPast' => $daySlots->isNotEmpty() && $daySlots->every(fn (array $slot) => $slot['past']),
                'slots' => $daySlots,
            ];

            $cursor->addDay();
        }

        $selectedDate = $this->resolveSelectedDate(
            $request->old('day', $request->query('day')),
            $days,
        );

        return view('public.calendar', [
            'month' => $month,
            'monthLabel' => $month->format('F Y'),
            'prevMonth' => $month->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $month->copy()->addMonth()->format('Y-m'),
            'days' => $days,
            'selectedDate' => $selectedDate,
            'selectedSlotId' => $request->old('slot_id'),
        ]);
    }

    private function resolveMonth(?string $value): Carbon
    {
        if (is_string($value) && preg_match('/^\d{4}-\d{2}$/', $value)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $value.'-01')->startOfMonth();
            } catch (\Throwable) {
                // fall through
            }
        }

        return now()->startOfMonth();
    }

    /**
     * @param  list<array<string, mixed>>  $days
     */
    private function resolveSelectedDate(mixed $requested, array $days): ?string
    {
        $dates = collect($days);

        if (is_string($requested) && $dates->contains(fn (array $day) => $day['date'] === $requested && $day['slotCount'] > 0)) {
            return $requested;
        }

        $today = $dates->first(fn (array $day) => $day['isToday'] && $day['openCount'] > 0);

        if ($today) {
            return $today['date'];
        }

        $open = $dates->first(fn (array $day) => $day['openCount'] > 0);

        return $open['date'] ?? null;
    }
}
