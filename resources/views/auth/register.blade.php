@extends('layouts.auth')

@section('title', 'Daftar - TaskFlow')

@section('content')

<div class="auth-page auth-register-page">

    <div class="auth-brand">
        <img
            src="{{ asset('images/brand/logo-horizontal.svg') }}"
            alt="TaskFlow"
            class="auth-brand-logo"
            width="205"
            height="50"
        >
        <h1>Mulai kerja lebih teratur</h1>
        <p class="auth-tagline">Work Smarter. Flow Better.</p>
        <p class="auth-intro">Buat akun dan satukan pekerjaan, tim, serta progres dalam satu ruang kerja.</p>
    </div>

    <div class="panel auth-card">

        <div class="panel-head">
            <div>
                <h2>Buat akun TaskFlow</h2>
                <p>Isi informasi berikut untuk memulai.</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-error auth-register-errors">
                <ul class="auth-errors">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="name">Nama</label>
                <input
                    id="name"
                    type="text"
                    name="name"
                    class="form-control"
                    value="{{ old('name') }}"
                    autocomplete="name"
                    placeholder="Nama lengkap"
                    required
                    autofocus
                >
            </div>

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
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    class="form-control"
                    autocomplete="new-password"
                    placeholder="Buat password"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    class="form-control"
                    autocomplete="new-password"
                    placeholder="Ulangi password"
                    required
                >
            </div>

            <button type="submit" class="btn primary auth-submit">
                Buat akun
            </button>
        </form>

        <div class="auth-register-switch">
            <span>Sudah punya akun?</span>
            <a href="{{ route('login') }}">Masuk</a>
        </div>
    </div>

    <p class="auth-footer">Ruang kerja yang lebih teratur dimulai dari sini.</p>

</div>

@endsection
