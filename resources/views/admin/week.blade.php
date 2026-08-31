@extends('layouts.app')

@section('title', 'Week · SlotBook')
@section('body', 'is-week')

@section('content')
    <div class="week-stage">
        <div class="week-head">
            <a class="month-shift" href="{{ route('admin.week', ['week' => $prevWeek]) }}" aria-label="Previous week">Prev</a>
            <h1>{{ $weekLabel }}</h1>
            <a class="month-shift" href="{{ route('admin.week', ['week' => $nextWeek]) }}" aria-label="Next week">Next</a>
        </div>

        @php
            $hasAny = collect($days)->contains(fn ($day) => $day['slots']->isNotEmpty());
        @endphp

        @if (! $hasAny)
            <p class="empty-band">Quiet week. Seed the book, or move to another week.</p>
        @endif

        <p class="week-hint">Swipe across the week.</p>

        <div class="week-grid">
            @foreach ($days as $day)
                <section class="day-col" @class(['is-today' => $day['isToday']])>
                    <header>
                        <span class="dow-label">{{ $day['label'] }}</span>
                        <span class="day-num">{{ $day['num'] }}</span>
                    </header>

                    @forelse ($day['slots'] as $slot)
                        @php
                            $active = $slot->bookings->first(fn ($booking) => $booking->status->isActive());
                            $latest = $active ?? $slot->bookings->first();
                        @endphp
                        <article class="slot-row" @class([
                            'is-open' => $active === null && $slot->starts_at->isFuture(),
                            'is-pending' => $active?->isPending(),
                            'is-confirmed' => $active?->isConfirmed(),
                            'is-past' => $slot->starts_at->isPast() && $active === null,
                        ])>
                            <p class="slot-time">{{ $slot->starts_at->format('g:i') }}<span>{{ $slot->starts_at->format('A') }}</span></p>

                            @if ($latest)
                                <p class="slot-guest">
                                    <strong>{{ $latest->guest_name }}</strong>
                                    <span>{{ $latest->guest_email }}</span>
                                </p>
                                <p class="slot-flags">
                                    <span class="flag">{{ $latest->status->label() }}</span>
                                    @if ($latest->paid)
                                        <span class="flag is-paid">Paid</span>
                                    @endif
                                </p>
                                @if (! $latest->isCancelled())
                                    <div class="slot-actions">
                                        @if ($latest->isPending())
                                            <form method="POST" action="{{ route('admin.bookings.confirm', $latest) }}">
                                                @csrf
                                                <button type="submit">Confirm</button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.bookings.cancel', $latest) }}">
                                            @csrf
                                            <button type="submit">Cancel</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.bookings.paid', $latest) }}">
                                            @csrf
                                            <button type="submit">{{ $latest->paid ? 'Unmark paid' : 'Mark paid' }}</button>
                                        </form>
                                    </div>
                                @endif
                            @else
                                <p class="slot-guest is-empty">Open hour</p>
                            @endif
                        </article>
                    @empty
                        <p class="empty-copy">No hours.</p>
                    @endforelse
                </section>
            @endforeach
        </div>
    </div>
@endsection
