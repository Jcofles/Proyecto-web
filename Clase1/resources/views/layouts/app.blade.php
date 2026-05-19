<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'UniMaps') }}</title>
    <meta name="description" content="Sistema de navegación geoespacial para el campus UniMaps con GPS de alta precisión">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#ffffff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="UniMaps">
    
    <!-- iOS Icons -->
    <link rel="apple-touch-icon" sizes="192x192" href="/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="512x512" href="/icon-512x512.png">
    
    <!-- Manifest PWA -->
    <link rel="manifest" href="/manifest.json">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/icon-192x192.png">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @yield('content')
</body>
</html>
