@extends('layouts.auth')

@section('title', 'Login - TaskFlow')

@section('content')

<div class="auth-page">

    <div class="panel auth-card">

        <div class="panel-head">
            <div>
                <h2>Login</h2>
                <p>Masuk ke TaskFlow</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-error">
                <ul class="auth-errors">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>

                <input
                    id="email"
                    type="email"
                    name="email"
                    class="form-control"
                    value="{{ old('email') }}"
                    autocomplete="email"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>

                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="btn primary auth-submit">
                Login
            </button>

        </form>

        <div class="auth-register">
            <span>Belum punya akun?</span>
            <a href="{{ route('register') }}">Register</a>
        </div>

    </div>

</div>

@endsection