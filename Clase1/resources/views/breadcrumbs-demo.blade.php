@extends('layouts.app')

@section('content')
<div class="container mx-auto px-6 py-8">
    {{-- Ejemplo 1: Breadcrumbs Simple --}}
    <x-breadcrumbs :items="[
        ['title' => 'Inicio', 'url' => route('welcome')],
        ['title' => 'Dashboard', 'url' => '#'],
        ['title' => 'Nodos', 'url' => '#'],
        ['title' => 'Crear Nodo']
    ]" />
    
    {{-- Ejemplo 2: Breadcrumbs con Estilo --}}
    <x-breadcrumbs-styled :items="[
        [
            'title' => 'Inicio', 
            'url' => route('welcome'),
            'icon' => '<svg class=\"w-4 h-4\" fill=\"currentColor\" viewBox=\"0 0 20 20\"><path d=\"M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z\"></path></svg>'
        ],
        [
            'title' => 'Mapas', 
            'url' => '#',
            'icon' => '<svg class=\"w-4 h-4\" fill=\"currentColor\" viewBox=\"0 0 20 20\"><path fill-rule=\"evenodd\" d=\"M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z\" clip-rule=\"evenodd\"></path></svg>'
        ],
        ['title' => 'Campus ITFIP']
    ]" />
    
    {{-- Ejemplo 3: Breadcrumbs Minimalista --}}
    <x-breadcrumbs-minimal :items="[
        ['title' => 'Inicio', 'url' => route('welcome')],
        ['title' => 'Configuración', 'url' => '#'],
        ['title' => 'Usuarios']
    ]" />
    
    {{-- Ejemplo 4: Usando el Service --}}
    <x-breadcrumbs :items="App\Services\BreadcrumbService::custom([
        ['title' => 'Mi Sección', 'url' => '#'],
        ['title' => 'Página Actual']
    ])" />
    
    <div class="bg-white dark:bg-[#161615] rounded-lg p-6 shadow-sm border border-[#e3e3e0] dark:border-[#3E3E3A]">
        <h1 class="text-2xl font-bold text-[#1b1b18] dark:text-[#EDEDEC] mb-4">
            Ejemplos de Breadcrumbs para UniMaps
        </h1>
        
        <div class="space-y-4 text-[#706f6c] dark:text-[#A1A09A]">
            <p>Los breadcrumbs están diseñados para seguir la temática visual de tu proyecto:</p>
            <ul class="list-disc list-inside space-y-2 ml-4">
                <li><strong>Color principal:</strong> #00ff88 (verde ITFIP)</li>
                <li><strong>Tipografía:</strong> Instrument Sans</li>
                <li><strong>Modo oscuro:</strong> Soporte completo</li>
                <li><strong>Responsive:</strong> Adaptable a móviles</li>
            </ul>
        </div>
    </div>
</div>
@endsection