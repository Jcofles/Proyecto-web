<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ITFIP Maps') }}</title>
    <meta name="description" content="Sistema de navegación geoespacial para el campus ITFIP con GPS de alta precisión">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#00ff88">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="ITFIP Maps">
    
    <!-- iOS Icons -->
    <link rel="apple-touch-icon" href="/icon-192x192.svg">
    <link rel="apple-touch-icon" sizes="192x192" href="/icon-192x192.svg">
    <link rel="apple-touch-icon" sizes="512x512" href="/icon-512x512.svg">
    
    <!-- Manifest -->
    <link rel="manifest" href="/manifest.webmanifest">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/icon-192x192.svg">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @yield('content')
</body>
</html>
