<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f3efe7">
    <meta name="description" content="Pick a day, pick an hour, hold it. SlotBook is a calm booking calendar.">
    <title>@yield('title', 'SlotBook')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,600;1,6..72,400;1,6..72,600&family=Source+Sans+3:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/slotbook.css') }}">
</head>
<body class="@yield('body')">
    <a class="skip-link" href="#content">Skip to the calendar</a>

    <header class="masthead">
        <a class="wordmark" href="{{ route('home') }}">SlotBook</a>
        <nav class="mast-nav" aria-label="Primary">
            @auth
                <a href="{{ route('admin.week') }}" @class(['is-current' => request()->routeIs('admin.week')])>Week</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Log out</button>
                </form>
            @else
                <a href="{{ route('login') }}" @class(['is-current' => request()->routeIs('login')])>Admin</a>
            @endauth
        </nav>
    </header>

    @if (session('status') && ! request()->routeIs('home'))
        <p class="flash" role="status">{{ session('status') }}</p>
    @endif

    <main id="content">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
