@extends('layouts.app')

@section('title', $monthLabel.' · SlotBook')
@section('body', 'is-calendar')

@section('content')
    @php
        $hasHours = collect($days)->contains(fn ($day) => $day['slotCount'] > 0);
        $nameInvalid = $errors->has('name');
        $emailInvalid = $errors->has('email');
    @endphp

    <div
        class="stage"
        x-data="slotbook({
            days: {{ \Illuminate\Support\Js::from($days) }},
            selectedDate: {{ \Illuminate\Support\Js::from($selectedDate) }},
            selectedSlotId: {{ \Illuminate\Support\Js::from($selectedSlotId ? (int) $selectedSlotId : null) }},
        })"
    >
        <div class="month-head">
            <a class="month-shift" href="{{ route('home', ['month' => $prevMonth]) }}" aria-label="Previous month">Prev</a>
            <h1>{{ $monthLabel }}</h1>
            <a class="month-shift" href="{{ route('home', ['month' => $nextMonth]) }}" aria-label="Next month">Next</a>
        </div>

        @if (! $hasHours)
            <p class="empty-band">Nothing on the book this month. Try another month, or ask Ana to seed the week.</p>
        @endif

        <div class="dow" aria-hidden="true">
            <span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span><span>Sun</span>
        </div>

        <div class="month-grid" role="grid" aria-label="{{ $monthLabel }}">
            @foreach ($days as $day)
                <button
                    type="button"
                    class="day"
                    role="gridcell"
                    @class([
                        'is-out' => ! $day['inMonth'],
                        'is-today' => $day['isToday'],
                        'has-open' => $day['openCount'] > 0,
                        'is-muted' => $day['slotCount'] === 0,
                    ])
                    :class="{ 'is-selected': selectedDate === '{{ $day['date'] }}' }"
                    @if ($day['slotCount'] === 0) disabled @endif
                    :aria-pressed="selectedDate === '{{ $day['date'] }}'"
                    aria-label="{{ $day['date'] }}{{ $day['openCount'] ? ', '.$day['openCount'].' open' : '' }}"
                    @click="selectDayByDate('{{ $day['date'] }}')"
                >
                    <span class="day-num">{{ $day['day'] }}</span>
                    @if ($day['openCount'] > 0)
                        <span class="day-mark">{{ $day['openCount'] }} open</span>
                    @elseif (! empty($day['allPast']))
                        <span class="day-mark is-quiet">Passed</span>
                    @elseif ($day['slotCount'] > 0)
                        <span class="day-mark is-quiet">Full</span>
                    @endif
                </button>
            @endforeach
        </div>

        <section
            class="ledger"
            x-ref="ledger"
            :class="{ 'is-open': selectedDay }"
            :hidden="!selectedDay"
            aria-live="polite"
        >
            <div class="ledger-inner">
                <header class="ledger-head">
                    <div>
                        <p class="kicker">On the calendar</p>
                        <h2 x-text="selectedDay ? formatDate(selectedDay.date) : ''"></h2>
                    </div>
                    <button type="button" class="text-btn" @click="closeDay()">Close</button>
                </header>

                @if (session('status'))
                    <p class="flash in-ledger" role="status">{{ session('status') }}</p>
                @endif

                @if ($errors->any())
                    <ul class="form-errors" id="book-errors" role="alert">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif

                <template x-if="selectedDay && selectedDay.slots.length === 0">
                    <p class="empty-copy">No hours on this day.</p>
                </template>

                <template x-if="selectedDay && selectedDay.allPast && selectedDay.openCount === 0 && selectedDay.slots.length > 0">
                    <p class="empty-copy">Those hours have passed. Pick another day.</p>
                </template>

                <template x-if="selectedDay && !selectedDay.allPast && selectedDay.openCount === 0 && selectedDay.slots.length > 0">
                    <p class="empty-copy">Those hours are taken. Pick another day.</p>
                </template>

                <div class="hours" x-show="selectedDay" role="list">
                    <template x-for="slot in (selectedDay ? selectedDay.slots : [])" :key="slot.id">
                        <button
                            type="button"
                            class="hour"
                            role="listitem"
                            :class="{ 'is-picked': selectedSlotId === slot.id, 'is-shut': !slot.open }"
                            :disabled="!slot.open"
                            :aria-pressed="selectedSlotId === slot.id"
                            @click="selectSlot(slot)"
                        >
                            <span x-text="slot.range"></span>
                            <small x-text="slot.open ? 'Open' : (slot.past ? 'Passed' : 'Taken')"></small>
                        </button>
                    </template>
                </div>

                <form
                    class="book-form"
                    method="POST"
                    action="{{ route('bookings.store') }}"
                    x-show="selectedSlot"
                    x-cloak
                >
                    @csrf
                    <input type="hidden" name="day" :value="selectedDate">
                    <input type="hidden" name="slot_id" :value="selectedSlotId">

                    <p class="kicker">Hold <span x-text="selectedSlot ? selectedSlot.time : ''"></span></p>
                    <label>
                        Name
                        <input
                            type="text"
                            name="name"
                            x-ref="nameField"
                            value="{{ old('name') }}"
                            required
                            maxlength="120"
                            autocomplete="name"
                            @if ($nameInvalid) aria-invalid="true" aria-describedby="book-errors" @endif
                        >
                    </label>
                    <label>
                        Email
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            maxlength="255"
                            autocomplete="email"
                            @if ($emailInvalid) aria-invalid="true" aria-describedby="book-errors" @endif
                        >
                    </label>
                    <button type="submit" class="submit">Book this hour</button>
                </form>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script defer src="{{ asset('js/slotbook.js') }}"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
@endpush
