@extends('layouts.app')

@section('title', 'Admin · SlotBook')
@section('body', 'is-login')

@section('content')
    <div class="login-stage">
        <p class="kicker">The book</p>
        <h1>Sign in to the week.</h1>
        <p class="lede">Confirm, cancel, or mark an hour paid. Guests never see this page.</p>

        @if ($errors->any())
            <ul class="form-errors" id="login-errors" role="alert">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form class="login-form" method="POST" action="{{ route('login.store') }}">
            @csrf
            <label>
                Email
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autocomplete="username"
                    @if ($errors->has('email')) aria-invalid="true" aria-describedby="login-errors" @endif
                >
            </label>
            <label>
                Password
                <input
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    @if ($errors->has('email')) aria-invalid="true" aria-describedby="login-errors" @endif
                >
            </label>
            <label class="check">
                <input type="checkbox" name="remember" value="1">
                Keep me signed in
            </label>
            <button type="submit" class="submit">Enter the week</button>
        </form>
    </div>
@endsection
