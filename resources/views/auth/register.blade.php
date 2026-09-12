@extends('layouts.auth')

@section('title', 'Register - TaskFlow')

@section('content')

<div class="auth-page">

<div class="panel auth-card">

    <div class="panel-head">
        <div>
            <h2>Register</h2>
            <p>Buat akun TaskFlow</p>
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
                required
            >
        </div>

        <button type="submit" class="btn primary auth-submit">
            Daftar
        </button>
		<div class="auth-register">
        <span>Sudah punya akun?</span>
        <a href="{{ route('login') }}">Login</a>
    </div>

    </form>

</div>

</div>

@endsection