<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'TaskFlow')</title>

    <link rel="stylesheet" href="{{ asset('css/task-manager.css') }}">
</head>

<body class="auth-layout">

    <main class="auth-container">
        @yield('content')
    </main>

</body>
</html>