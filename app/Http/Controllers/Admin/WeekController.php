<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class WeekController extends Controller
{
    public function __invoke(Request $request): View
    {
        $anchor = $this->resolveAnchor($request->query('week'));
        $start = $anchor->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $end = $start->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $slots = Slot::query()
            ->with(['bookings' => fn ($query) => $query->orderByDesc('created_at')])
            ->where('starts_at', '>=', $start->format('Y-m-d H:i:s'))
            ->where('starts_at', '<=', $end->format('Y-m-d H:i:s'))
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (Slot $slot) => $slot->starts_at->copy()->timezone((string) config('app.timezone'))->toDateString());

        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->toDateString();

            $days[] = [
                'date' => $key,
                'label' => $date->format('D'),
                'num' => $date->format('j'),
                'isToday' => $date->isToday(),
                'slots' => $slots->get($key, collect()),
            ];
        }

        return view('admin.week', [
            'weekLabel' => $start->format('j M').' – '.$end->format('j M Y'),
            'prevWeek' => $start->copy()->subWeek()->toDateString(),
            'nextWeek' => $start->copy()->addWeek()->toDateString(),
            'days' => $days,
        ]);
    }

    private function resolveAnchor(?string $value): Carbon
    {
        if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
            } catch (\Throwable) {
                // fall through
            }
        }

        return now()->startOfDay();
    }
}
