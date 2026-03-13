<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'Food Ordering System') }}</title>
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-blue.png') }}?v=20260313">
    <link rel="shortcut icon" href="{{ asset('favicon-blue.png') }}?v=20260313">
    <link rel="apple-touch-icon" href="{{ asset('favicon-blue.png') }}?v=20260313">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @inertiaHead
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900">
    @inertia
</body>
</html>
