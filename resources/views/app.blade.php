<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/images/favicon-bateng.png">

    <!-- Title -->
    <title inertia>{{ config('app.name', 'Bedulang') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
        rel="stylesheet"
    />

    <!-- Stack Sans Headline (Headers) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Stack+Sans+Headline:wght@200..700&display=swap" rel="stylesheet">

    <!-- Routes -->
    @routes

    <!-- Assets -->
    @vite([
        'resources/js/app.js',
        "resources/js/Pages/{$page['component']}.vue"
    ])

    <!-- Inertia Head -->
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>
</html>
