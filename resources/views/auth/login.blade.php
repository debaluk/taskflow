@extends('layouts.auth')

@section('title', 'Login - TaskFlow')

@section('content')

<div class="auth-page auth-login-page">

    <div class="auth-brand">
        <img
            src="{{ asset('images/brand/logo-horizontal.svg') }}"
            alt="TaskFlow"
            class="auth-brand-logo"
            width="205"
            height="50"
        >
        <h1>Selamat datang kembali</h1>
        <p class="auth-tagline">Work Smarter. Flow Better.</p>
    </div>

    <div class="panel auth-card">

        <div class="panel-head">
            <div>
                <h2>Masuk ke akun Anda</h2>
                <p>Lanjutkan pekerjaan dan kolaborasi Anda di TaskFlow.</p>
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
                    placeholder="nama@email.com"
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
                    placeholder="Masukkan password"
                    required
                >
            </div>

            <button type="submit" class="btn primary auth-submit">
                Masuk ke TaskFlow
            </button>

        </form>

        <div class="auth-register">
            <span>Belum punya akun?</span>
            <a href="{{ route('register') }}">Register</a>
        </div>

    </div>

    <p class="auth-footer">Ruang kerja yang lebih teratur dimulai dari sini.</p>

</div>

@endsection