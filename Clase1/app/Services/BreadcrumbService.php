<?php

namespace App\Services;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class BreadcrumbService
{
    public static function generate($customItems = [])
    {
        $breadcrumbs = [];
        $currentRoute = Route::currentRouteName();
        $segments = request()->segments();
        
        // Siempre agregar inicio
        $breadcrumbs[] = [
            'title' => 'Inicio',
            'url' => route('welcome'),
            'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>'
        ];
        
        // Mapeo de rutas a títulos
        $routeTitles = [
            'dashboard' => 'Dashboard',
            'nodos' => 'Nodos',
            'nodos.index' => 'Lista de Nodos',
            'nodos.create' => 'Crear Nodo',
            'nodos.show' => 'Ver Nodo',
            'nodos.edit' => 'Editar Nodo',
            'usuarios' => 'Usuarios',
            'usuarios.index' => 'Lista de Usuarios',
            'usuarios.create' => 'Crear Usuario',
            'usuarios.show' => 'Ver Usuario',
            'usuarios.edit' => 'Editar Usuario',
            'mapas' => 'Mapas',
            'configuracion' => 'Configuración',
        ];
        
        // Generar breadcrumbs basado en segmentos
        $url = '';
        foreach ($segments as $index => $segment) {
            $url .= '/' . $segment;
            $title = $routeTitles[$segment] ?? Str::title(str_replace('-', ' ', $segment));
            
            // No agregar el último segmento si es un ID numérico
            if ($index === count($segments) - 1 && is_numeric($segment)) {
                continue;
            }
            
            $breadcrumbs[] = [
                'title' => $title,
                'url' => url($url)
            ];
        }
        
        // Agregar items personalizados
        if (!empty($customItems)) {
            $breadcrumbs = array_merge($breadcrumbs, $customItems);
        }
        
        return $breadcrumbs;
    }
    
    public static function custom($items)
    {
        $breadcrumbs = [
            [
                'title' => 'Inicio',
                'url' => route('welcome'),
                'icon' => '<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>'
            ]
        ];
        
        return array_merge($breadcrumbs, $items);
    }
}