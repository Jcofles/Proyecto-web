# 📚 Documentación Técnica - Implementaciones del Proyecto ITFIP Maps

**Versión:** 1.0  
**Fecha:** 26 de marzo de 2026  
**Proyecto:** ITFIP Maps - Sistema de Navegación y Ubicación  
**Equipo:** Desarrollo Backend (Laravel)

---

## 📋 Tabla de Contenidos

1. [Introducción](#introducción)
2. [Validaciones Profesionales - Form Requests](#1-validaciones-profesionales-form-requests)
3. [Filtros y Buscador en Tiempo Real](#2-filtros-y-buscador-en-tiempo-real)
4. [Regla Sometimes para Contraseñas](#3-regla-sometimes-para-contraseñas)
5. [Seeders y Factories con Faker](#4-seeders-y-factories-con-faker)
6. [Flujo de Integración Completo](#5-flujo-de-integración-completo)
7. [Conclusiones y Mejores Prácticas](#conclusiones)

---

## 🎯 Introducción

Este documento detalla las cuatro implementaciones principales realizadas en el proyecto ITFIP Maps para mejorar la calidad, seguridad y mantenibilidad del código:

- **Form Requests**: Centralización y profesionalización de validaciones
- **Buscador en Tiempo Real**: Filtros avanzados con validación integrada
- **Regla Sometimes**: Validaciones condicionales para campos opcionales
- **Faker & Seeders**: Generación profesional de datos de prueba

Cada sección incluye explicaciones teóricas, ejemplos de código real del proyecto y guías de uso.

---

# 1. VALIDACIONES PROFESIONALES - FORM REQUESTS

## 🎓 Concepto Teórico

Las **Form Requests** son clases de Laravel que encapsulan toda la lógica de validación de una acción específica. En lugar de validar directamente en el controlador, delegamos esa responsabilidad a una clase dedicada.

### ¿Por qué Form Requests?

| Aspecto | Sin Form Request | Con Form Request |
|--------|------------------|------------------|
| **Ubicación** | Controlador (mezclado) | Clase dedicada (separado) |
| **Reutilización** | No reutilizable | Reutilizable en otros métodos |
| **Mensajes** | Repetidos en controladores | Centralizados |
| **Validación Automática** | Manual (if..fails) | Automática (Laravel) |
| **Testing** | Difícil de testear | Fácil de testear |
| **Responsabilidad** | Controlador sobrecargado | Única responsabilidad |

---

## 📝 Antes vs Después

### ❌ ANTES (Código Directo en Controlador)

```php
// RegisterController.php - ANTES
public function register(Request $request)
{
    // Validación manual mezclada con lógica
    $validator = Validator::make($request->all(), [
        'nombres' => 'required|string|max:191|regex:/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/',
        'apellidos' => 'required|string|max:191|regex:/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/',
        'email' => 'required|email|max:191',
        'password' => 'required|string|min:8',
        'password_confirmation' => 'required|string|same:password',
    ]);

    // Manejo manual de errores
    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validación fallida',
            'errors' => $validator->errors(),
        ], 422);
    }

    // Aquí recién comienza la lógica real de negocio
    $validated = $validator->validated();
    // ... resto de lógica
}
```

**Problemas:**
- 🔴 Controlador responsable de demasiadas cosas
- 🔴 Código repetido en múltiples métodos
- 🔴 Difícil de testear
- 🔴 Lógica de validación y negocio mezcladas

---

### ✅ DESPUÉS (Con Form Request)

```php
// RegisterRequest.php - NUEVA CLASE
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Todos pueden registrarse
    }

    public function rules(): array
    {
        return [
            'nombres' => 'required|string|max:191|regex:/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/',
            'apellidos' => 'required|string|max:191|regex:/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/',
            'email' => 'required|email|max:191',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
        ];
    }

    public function messages(): array
    {
        return [
            'nombres.required' => 'El nombre es requerido',
            'nombres.regex' => 'El nombre solo puede contener letras y espacios',
            'email.required' => 'El correo es requerido',
            'email.email' => 'El correo debe ser válido',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
        ];
    }
}

// RegisterController.php - AHORA LIMPIO
public function register(RegisterRequest $request)
{
    // Laravel valida automáticamente.
    // Si falla, retorna 422 automáticamente.
    // Si pasa, recibimos los datos validados:
    
    $validated = $request->validated(); // Datos ya validados
    
    // Aquí solo va la lógica de negocio
    $pending = PendingUser::create([
        'nombres' => $validated['nombres'],
        'apellidos' => $validated['apellidos'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
    ]);
    
    // ... resto de lógica
}
```

**Ventajas:**
- ✅ Separación clara de responsabilidades
- ✅ Código reutilizable
- ✅ Mensajes personalizados centralizados
- ✅ Validación automática (Laravel)
- ✅ Fácil de testear
- ✅ Controlador limpio y enfocado

---

## 📂 Estructura de Form Requests en el Proyecto

```
app/Http/Requests/
├── RegisterRequest.php              → Registro de nuevos usuarios
├── LoginRequest.php                 → Login
├── VerifyEmailRequest.php           → Verificación de email
├── SendPasswordCodeRequest.php      → Envío de código de reseteo
├── VerifyPasswordCodeRequest.php    → Verificación de código
├── ResetPasswordRequest.php         → Cambio de contraseña
├── UpdateUserRequest.php            → Actualización de perfil
├── StoreNodoRequest.php             → Crear nodo/ubicación
├── ConectarNodoRequest.php          → Conectar dos nodos
├── FilterNodoRequest.php            → Filtros de búsqueda de nodos
└── FilterUserRequest.php            → Filtros de búsqueda de usuarios
```

---

## 🔍 Ejemplos Detallados del Proyecto

### Ejemplo 1: RegisterRequest

**Ubicación:** `app/Http/Requests/RegisterRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * En registro, cualquiera puede registrarse
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * 
     * Explicación de cada regla:
     * - required: Campo obligatorio
     * - string: Debe ser texto
     * - max:191: Máximo 191 caracteres (límite de BD)
     * - regex: Expresión regular para validar solo letras españolas
     * - email: Validar formato de email
     * - min:8: Mínimo 8 caracteres
     * - same:password: Debe coincidir con otro campo
     */
    public function rules(): array
    {
        return [
            'nombres' => 'required|string|max:191|regex:/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/',
            'apellidos' => 'required|string|max:191|regex:/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/',
            'email' => 'required|email|max:191',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
        ];
    }

    /**
     * Get custom messages for validator errors.
     * 
     * Laravel automáticamente traduce los errores.
     * Aquí personalizamos los mensajes en español.
     * 
     * Formato: 'campo.regla' => 'mensaje personalizado'
     */
    public function messages(): array
    {
        return [
            'nombres.required' => 'El nombre es requerido',
            'nombres.regex' => 'El nombre solo puede contener letras y espacios',
            'apellidos.required' => 'El apellido es requerido',
            'apellidos.regex' => 'El apellido solo puede contener letras y espacios',
            'email.required' => 'El correo es requerido',
            'email.email' => 'El correo debe ser válido',
            'password.required' => 'La contraseña es requerida',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password_confirmation.same' => 'Las contraseñas no coinciden',
        ];
    }
}
```

**Uso en Controlador:**

```php
// RegisterController.php
use App\Http\Requests\RegisterRequest; // ← Importar el FormRequest

public function register(RegisterRequest $request) // ← Inyectar directamente
{
    // Aquí ya sabemos que los datos son válidos
    $validated = $request->validated();
    
    // Crear registro pendiente
    $token = Str::random(64);
    $pending = PendingUser::create([
        'nombres' => $validated['nombres'],
        'apellidos' => $validated['apellidos'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'email_verification_token' => $token,
        'email_verification_expires_at' => Carbon::now()->addHours(24),
    ]);

    // Enviar email de verificación
    $this->emailService->sendVerification($pending, $token);

    return response()->json([
        'message' => 'Usuario registrado. Verifica tu correo.',
        'pending_id' => $pending->id,
    ], 201);
}
```

---

### Ejemplo 2: LoginRequest

**Ubicación:** `app/Http/Requests/LoginRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo es requerido',
            'email.email' => 'El correo debe ser válido',
            'password.required' => 'La contraseña es requerida',
        ];
    }
}
```

**Uso:**

```php
// LoginController.php
public function login(LoginRequest $request)
{
    $validated = $request->validated();
    
    $user = User::where('email', $validated['email'])->first();
    
    if (!$user || !Hash::check($validated['password'], $user->password)) {
        return response()->json([
            'message' => 'Credenciales incorrectas',
        ], 401);
    }
    
    $token = $user->createToken('auth_token')->plainTextToken;
    
    return response()->json([
        'message' => 'Login exitoso',
        'token' => $token,
    ], 200);
}
```

---

### Ejemplo 3: StoreNodoRequest

**Ubicación:** `app/Http/Requests/StoreNodoRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // O validar permisos aquí
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'latitud' => 'required|numeric|between:-90,90',
            'longitud' => 'required|numeric|between:-180,180',
            'tipo_id' => 'required|integer|exists:nodo_tipos,id',
            'piso' => 'required|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del nodo es requerido',
            'latitud.between' => 'La latitud debe estar entre -90 y 90',
            'longitud.between' => 'La longitud debe estar entre -180 y 180',
            'tipo_id.exists' => 'El tipo de nodo no existe en nuestra BD',
        ];
    }
}
```

**Uso:**

```php
// NodoController.php
public function store(StoreNodoRequest $request): JsonResponse
{
    $data = $request->validated(); // Todos los datos ya están validados
    
    $nodo = Nodo::create($data);
    
    return response()->json($nodo, 201);
}
```

---

## 🔐 Ciclo de Validación Automática

Cuando usas un Form Request, Laravel sigue este ciclo automáticamente:

```
1. Cliente envía POST /api/register
   {
     "nombres": "Juan",
     "apellidos": "Pérez",
     "email": "juan@example.com",
     "password": "segura123",
     "password_confirmation": "segura123"
   }
   ↓
2. Laravel intercepta la request
   ↓
3. Ejecuta las reglas de RegisterRequest
   ↓
4. ¿Validación OK?
   ├─ NO → Retorna automáticamente 422 con errores
   └─ SÍ → Continúa al método del controlador
   ↓
5. En el controlador:
   public function register(RegisterRequest $request)
   {
     // Aquí solo llega si validación pasó
     $validated = $request->validated();
     // ... lógica de negocio
   }
```

---

## ✅ Checklist de Form Requests en Proyecto

| Endpoint | Controlador | Form Request | Status |
|----------|-------------|--------------|--------|
| POST /api/register | RegisterController@register | RegisterRequest | ✅ |
| POST /api/login | LoginController@login | LoginRequest | ✅ |
| POST /api/verify-email | RegisterController@verifyEmail | VerifyEmailRequest | ✅ |
| POST /api/password/send-code | PasswordResetController@sendCode | SendPasswordCodeRequest | ✅ |
| POST /api/password/verify-code | PasswordResetController@verifyCode | VerifyPasswordCodeRequest | ✅ |
| POST /api/password/reset | PasswordResetController@resetPassword | ResetPasswordRequest | ✅ |
| PUT /api/users/{id} | UserController@update | UpdateUserRequest | ✅ |
| POST /api/nodos | NodoController@store | StoreNodoRequest | ✅ |
| POST /api/nodos/connect | NodoController@conectar | ConectarNodoRequest | ✅ |

---

---

# 2. FILTROS Y BUSCADOR EN TIEMPO REAL

## 🎓 Concepto Teórico

El **buscador en tiempo real** permite a los usuarios filtrar y buscar datos mientras escriben, sin recargar la página. En tu proyecto, implementamos esto mediante:

- **FilterRequest**: Validar parámetros de búsqueda/filtro
- **SearchController**: Lógica de búsqueda en BD
- **API REST**: Retorna JSON paginado
- **Vue.js**: Frontend consume y muestra resultados

### Nota sobre Livewire

**Livewire** es una alternativa históricamente, pero **NO es apropiada** para tu arquitectura:
- Livewire = Server-side rendering (Blade)
- Tu proyecto = API REST + Vue.js (SPA)
- Son dos paradigmas diferentes

La solución implementada (API + Vue) es **más moderna y escalable**.

---

## 📝 Estructura de Búsqueda

### Paso 1: FilterRequest (Validación)

**Ubicación:** `app/Http/Requests/FilterNodoRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterNodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Parámetros de búsqueda/filtro
     * Todos son 'sometimes' = opcional
     */
    public function rules(): array
    {
        return [
            'q' => 'sometimes|string|max:255',        // Búsqueda textual
            'tipo_id' => 'sometimes|integer|exists:nodo_tipos,id',  // Filtro por tipo
            'piso' => 'sometimes|integer',             // Filtro por piso
            'per_page' => 'sometimes|integer|min:1|max:100', // Paginación
        ];
    }

    public function messages(): array
    {
        return [
            'q.max' => 'La búsqueda no debe exceder 255 caracteres',
            'tipo_id.exists' => 'El tipo de nodo no existe',
            'per_page.max' => 'No se pueden traer más de 100 resultados',
        ];
    }
}
```

### Paso 2: SearchController (Lógica)

**Ubicación:** `app/Http/Controllers/Api/SearchController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\FilterNodoRequest;
use App\Http\Requests\FilterUserRequest;
use App\Models\Nodo;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class SearchController extends Controller
{
    /**
     * Buscar y filtrar nodos
     * 
     * GET /api/search/nodos?q=Salón&tipo_id=1&piso=2&per_page=10
     * 
     * @param FilterNodoRequest $request
     * @return JsonResponse
     */
    public function searchNodos(FilterNodoRequest $request): JsonResponse
    {
        // Los datos ya están validados por FilterNodoRequest
        $filters = $request->validated();
        
        // Comenzar con query vacío
        $query = Nodo::query();
        
        // Aplicar búsqueda por nombre
        if (!empty($filters['q'])) {
            $query->where('nombre', 'like', '%' . $filters['q'] . '%');
        }
        
        // Aplicar filtro por tipo
        if (!empty($filters['tipo_id'])) {
            $query->where('tipo_id', $filters['tipo_id']);
        }
        
        // Aplicar filtro por piso
        if (!empty($filters['piso'])) {
            $query->where('piso', $filters['piso']);
        }
        
        // Número de resultados por página
        $perPage = $filters['per_page'] ?? 15;
        
        // Ejecutar query y paginar
        $results = $query->paginate($perPage);
        
        return response()->json($results);
    }

    /**
     * Buscar y filtrar usuarios
     * 
     * GET /api/search/usuarios?q=Juan&status=activo&per_page=20
     * 
     * @param FilterUserRequest $request
     * @return JsonResponse
     */
    public function searchUsuarios(FilterUserRequest $request): JsonResponse
    {
        $filters = $request->validated();
        
        $query = User::query();
        
        // Búsqueda en múltiples campos
        if (!empty($filters['q'])) {
            $searchTerm = '%' . $filters['q'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nombres', 'like', $searchTerm)
                  ->orWhere('apellidos', 'like', $searchTerm)
                  ->orWhere('email', 'like', $searchTerm);
            });
        }
        
        // Filtro por estado
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        $perPage = $filters['per_page'] ?? 15;
        
        $results = $query->paginate($perPage);
        
        return response()->json($results);
    }
}
```

### Paso 3: Rutas API

**Ubicación:** `routes/api.php`

```php
<?php

use App\Http\Controllers\Api\SearchController;

// Búsqueda sin autenticación requerida
Route::get('/search/nodos', [SearchController::class, 'searchNodos']);

// Búsqueda solo para usuarios autenticados
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/search/usuarios', [SearchController::class, 'searchUsuarios']);
});
```

---

## 🎯 Ejemplos de Uso (Cliente/Frontend)

### Ejemplo 1: Búsqueda Simple desde Curl

```bash
# Buscar nodos que contengan "Salón"
curl "http://localhost:8000/api/search/nodos?q=Salón"

# Respuesta:
{
  "data": [
    {
      "id": 101,
      "nombre": "Salón 101",
      "latitud": 4.148,
      "longitud": -74.885,
      "tipo_id": 1,
      "piso": 1
    },
    {
      "id": 102,
      "nombre": "Salón 102",
      "latitud": 4.148,
      "longitud": -74.885,
      "tipo_id": 1,
      "piso": 1
    }
  ],
  "links": {...},
  "meta": {
    "current_page": 1,
    "total": 2,
    "per_page": 15
  }
}
```

### Ejemplo 2: Búsqueda Complejos desde Vue.js

```javascript
// En un componente Vue.js
<template>
  <div class="search-section">
    <input 
      v-model="searchQuery" 
      @input="handleSearch"
      placeholder="Buscar nodos..."
    />
    
    <select v-model="tipoId" @change="handleSearch">
      <option value="">Todos los tipos</option>
      <option value="1">Pasillo</option>
      <option value="2">Escaleras</option>
    </select>
    
    <div class="results">
      <div v-for="nodo in resultados" :key="nodo.id">
        {{ nodo.nombre }} - Piso {{ nodo.piso }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      searchQuery: '',
      tipoId: '',
      resultados: []
    }
  },
  methods: {
    async handleSearch() {
      // Construir parámetros
      const params = new URLSearchParams({
        ...(this.searchQuery && { q: this.searchQuery }),
        ...(this.tipoId && { tipo_id: this.tipoId }),
        per_page: 20
      });
      
      try {
        // Llamar a nuestro endpoint API
        const response = await fetch(`/api/search/nodos?${params}`);
        const data = await response.json();
        this.resultados = data.data;
      } catch (error) {
        console.error('Error en búsqueda:', error);
      }
    }
  }
}
</script>
```

---

## 📊 Flujo Completo de Búsqueda

```
┌─────────────────────────────────────────────────────────────┐
│ USUARIO ESCRIBE EN EL BUSCADOR (Vue.js)                     │
│ "Salón" en input de búsqueda                                │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ VUE.JS DISPARA EVENTO @input                                │
│ Llama a handleSearch()                                       │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ FETCH A BACKEND                                              │
│ GET /api/search/nodos?q=Salón&per_page=20                  │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ LARAVEL INTERCEPTA LA REQUEST                               │
│ routes/api.php → SearchController@searchNodos               │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ SEARCHCONTROLLER EJECUTA:                                   │
│ 1. SearchController inyecta FilterNodoRequest               │
│ 2. FilterNodoRequest valida parámetros                      │
│ 3. Si validación falla → return 422                         │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ BÚSQUEDA EN BD                                               │
│ $query = Nodo::query();                                     │
│ $query->where('nombre', 'like', '%Salón%');               │
│ $results = $query->paginate(20);                           │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ RESPUESTA JSON PAGINADA                                      │
│ {                                                           │
│   "data": [...],                                            │
│   "meta": { "total": 2, "current_page": 1 }               │
│ }                                                           │
└────────────────┬────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────┐
│ VUE.JS ACTUALIZA VISTA                                       │
│ this.resultados = data.data;                                │
│ Se renderiza lista de nodos encontrados                     │
└─────────────────────────────────────────────────────────────┘
```

---

## 📋 Parámetros de Búsqueda Disponibles

### Para Nodos: `/api/search/nodos`

| Parámetro | Tipo | Ejemplo | Descripción |
|-----------|------|---------|-------------|
| `q` | string | `?q=Salón` | Búsqueda por nombre |
| `tipo_id` | integer | `?tipo_id=1` | Filtrar por tipo (1=pasillo, 2=escaleras) |
| `piso` | integer | `?piso=2` | Filtrar por número de piso |
| `per_page` | integer | `?per_page=50` | Resultados por página (max 100) |

**Ejemplo combinado:**
```
GET /api/search/nodos?q=Paso&tipo_id=1&piso=1&per_page=10
```

### Para Usuarios: `/api/search/usuarios`

| Parámetro | Tipo | Ejemplo | Descripción |
|-----------|------|---------|-------------|
| `q` | string | `?q=Juan` | Busca en nombres, apellidos y email |
| `status` | string | `?status=activo` | Filtrar por estado (activo, inactivo, bloqueado, eliminado) |
| `per_page` | integer | `?per_page=25` | Resultados por página |

**Ejemplo:**
```
GET /api/search/usuarios?q=juan@example.com&status=activo
```

---

---

# 3. REGLA `SOMETIMES` PARA CONTRASEÑAS

## 🎓 Concepto Teórico

La regla `sometimes` en Laravel permite que un campo sea **validado SOLO si está presente** en la request.

### Diferencia: Required vs Sometimes

| Tipo | Comportamiento | Cuando Usar |
|------|---|---|
| `required` | Campo OBLIGATORIO. Si no está → Error | En creación (POST) |
| `sometimes` | Campo OPCIONAL. Solo valida si existe | En actualización (PUT/PATCH) |
| `required_if` | Obligatorio bajo condición | Si dependes de otro campo |

### Caso de Uso: Cambio de Contraseña

- **Al Registrar**: Password SÍ es obligatorio (required)
- **Al Actualizar Perfil**: Password NO es obligatorio (sometimes)

Si el usuario NO quiere cambiar su password, no lo envía.  
Si el usuario SÍ lo envía, lo validamos.

---

## 📝 Implementación en Proyecto

### UpdateUserRequest con `sometimes`

**Ubicación:** `app/Http/Requests/UpdateUserRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // O validar que sea el mismo usuario
    }

    /**
     * Reglas para actualizar usuario
     * 
     * Los campos pueden ser:
     * - 'required': Obligatorio siempre
     * - 'sometimes': Opcional, pero si llega se valida
     * - Sin regla: Se ignora
     */
    public function rules(): array
    {
        return [
            // Nombres y apellidos opcionales
            'nombres' => 'sometimes|string|max:191|regex:/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/',
            'apellidos' => 'sometimes|string|max:191|regex:/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/',
            
            // Email opcional pero único (excepto para este usuario)
            'email' => [
                'sometimes',
                'email',
                'max:191',
                Rule::unique('users')->ignore($this->user()?->id),
            ],
            
            // *** CONTRASEÑA CON SOMETIMES ***
            // Si el usuario envía password, validar:
            // - Mínimo 8 caracteres
            // - Debe coincidir con password_confirmation
            'password' => 'sometimes|string|min:8|confirmed',
            
            // Campo de confirmación, sin reglas propias
            'password_confirmation' => 'sometimes|string|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'nombres.regex' => 'El nombre solo puede contener letras y espacios',
            'apellidos.regex' => 'El apellido solo puede contener letras y espacios',
            'email.email' => 'El correo debe ser válido',
            'email.unique' => 'El correo ya está registrado',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden',
        ];
    }
}
```

---

## 📊 Comparación de Escenarios

### Escenario 1: Usuario NO envía contraseña

```json
{
  "nombres": "Juan",
  "apellidos": "Pérez",
  "email": "juan@example.com"
  // ❌ NO envía password
}
```

**Resultado:**
- ✅ `password` es ignorado (sometimes)
- ✅ Se actualizan nombres, apellidos, email
- ✅ La contraseña anterior se mantiene

---

### Escenario 2: Usuario SÍ envía contraseña

```json
{
  "nombres": "Juan",
  "apellidos": "Pérez",
  "email": "juan@example.com",
  "password": "nuevaSegura123",
  "password_confirmation": "nuevaSegura123"
}
```

**Resultado:**
- ✅ `password` se valida:
  - ¿Mínimo 8 caracteres? ✅ SÍ (14 caracteres)
  - ¿Coincide con confirmation? ✅ SÍ
- ✅ Se actualiza todo, incluyendo contraseña

---

### Escenario 3: Usuario envía contraseña inválida

```json
{
  "nombres": "Juan",
  "email": "juan@example.com",
  "password": "123",  // ❌ Solo 3 caracteres
  "password_confirmation": "123"
}
```

**Resultado:**
```json
{
  "message": "Validations failed",
  "errors": {
    "password": ["La contraseña debe tener al menos 8 caracteres"]
  }
  // ❌ Falla antes de actualizar
}
```

---

## 🔍 Código de Controlador

```php
// UserController.php (Ejemplo de uso)

use App\Http\Requests\UpdateUserRequest;

public function update($userId, UpdateUserRequest $request)
{
    // Los datos ya están validados
    $validated = $request->validated();
    
    // Obtener usuario
    $user = User::findOrFail($userId);
    
    // Actualizar solo los campos que enviaron
    if (isset($validated['nombres'])) {
        $user->nombres = $validated['nombres'];
    }
    
    if (isset($validated['apellidos'])) {
        $user->apellidos = $validated['apellidos'];
    }
    
    if (isset($validated['email'])) {
        $user->email = $validated['email'];
    }
    
    // *** SI ENVIARON PASSWORD ***
    if (isset($validated['password'])) {
        $user->password = Hash::make($validated['password']);
    }
    
    $user->save();
    
    return response()->json([
        'message' => 'Perfil actualizado exitosamente',
        'user' => $user
    ], 200);
}
```

---

## 📝 Otros FormRequests con `sometimes`

### ResetPasswordRequest

```php
public function rules(): array
{
    return [
        'email' => 'required|email',
        'code' => 'required|string|size:6',
        'password' => 'required|string|min:8|confirmed',
    ];
    // Aquí 'required' porque es un endpoint específico de cambio de password
}
```

### ConectarNodoRequest

```php
public function rules(): array
{
    return [
        'nodo_origen_id' => 'required|integer|exists:nodos,id',
        'nodo_destino_id' => 'required|integer|exists:nodos,id|different:nodo_origen_id',
        'distancia' => 'required|numeric|min:0',
        'bidireccional' => 'sometimes|boolean',  // ← SOMETIMES: opcional
    ];
}
```

---

## ✅ Cuándo Usar Sometimes

| Situación | Usar | Razón |
|-----------|------|-------|
| POST /register | `required` | Necesita todos los datos |
| PUT /users/{id} | `sometimes` | Actualización parcial |
| PATCH /users/{id} | `sometimes` | Actualización parcial |
| POST /password/reset | `required` | Password es el objetivo |
| POST /nodos/connect | `sometimes` (para `bidireccional`) | Campo opcional |

---

---

# 4. SEEDERS Y FACTORIES CON FAKER

## 🎓 Concepto Teórico

### ¿Qué son Factories?

Las **Factories** son clases que generan datos ficticios realistas para testing y development. Usan la librería **Faker** para generar datos aleatorios.

### ¿Qué son Seeders?

Los **Seeders** populan la base de datos con datos iniciales. Pueden usar Factories o queries directas.

### Flujo de Datos

```
Faker (genera datos aleatorios)
    ↓
Factory (formatea los datos)
    ↓
Seeder (inserta en BD) o Tests (testing)
```

---

## 📁 Estructura en el Proyecto

```
database/
├── factories/
│   └── UserFactory.php         ← Genera usuarios ficticios
└── seeders/
    ├── DatabaseSeeder.php      ← Orquesta seeders
    ├── CampusItfipSeeder.php   ← Carga nodos del campus
    └── NodoSeeder.php          ← Crea nodos de Bloque D
```

---

## 🏭 UserFactory - Generando Usuarios

**Ubicación:** `database/factories/UserFactory.php`

```php
<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     * Se guarda así para mantener consistencia en tests
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     * 
     * Aquí Faker genera datos aleatorios
     * 
     * fake()->name()              → "Dr. Carroll Jenkins"
     * fake()->unique()->safeEmail() → "alice@example.com"
     * fake()->unique()->safeEmail() → "bob@example.com"
     * now()                       → Carbon timestamp actual
     * Str::random(10)             → Token de 10 caracteres
     * 
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Nombre generado aleatoriamente
            'name' => fake()->name(),
            
            // Email único y seguro (no contains + signs)
            'email' => fake()->unique()->safeEmail(),
            
            // Email ya verificado por defecto
            'email_verified_at' => now(),
            
            // Contraseña hasheada (todas las mismas en tests)
            'password' => static::$password ??= Hash::make('password'),
            
            // Token para "remember me"
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     * 
     * Permite crear usuarios sin email verificado:
     * User::factory()->unverified()->create()
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
```

### ¿Qué hace Faker?

```php
// Ejemplos de generación de datos aleatorios
fake()->name()                    // "Mr. Jarod Jacobs V"
fake()->firstName()               // "Eleanore"
fake()->lastName()                // "Grimes"
fake()->email()                   // "wwalsh@example.net"
fake()->unique()->safeEmail()     // Garantiza email único
fake()->phoneNumber()             // "1-541-754-3010"
fake()->address()                 // "9356 Stokes Radial, Lismouth, RI 03605"
fake()->sentence()                // "Doloribus qui rerum nulla necessitatibus."
fake()->paragraph()               // Párrafo lorem ipsum completo
fake()->numberBetween(1, 100)     // Número entre 1 y 100
fake()->date()                    // "1972-02-05"
fake()->time()                    // "12:33:36"
```

---

## 📊 Seeders - Poblando la Base de Datos

### DatabaseSeeder (Principal)

**Ubicación:** `database/seeders/DatabaseSeeder.php`

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents; // Desactiva eventos para seeding (más rápido)

    /**
     * Seed the application's database.
     * 
     * Este es el punto de entrada para todos los seeders.
     * Se ejecuta con: php artisan db:seed
     */
    public function run(): void
    {
        // Llamar a otros seeders en orden
        $this->call(CampusItfipSeeder::class);
        // Podrías agregar más:
        // $this->call(UserSeeder::class);
        // $this->call(NodoSeeder::class);
    }
}
```

---

### CampusItfipSeeder - Nodos del Campus

**Ubicación:** `database/seeders/CampusItfipSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CampusItfipSeeder extends Seeder
{
    /**
     * Seed los nodos del campus ITFIP
     * 
     * Crea 51 nodos recorriendo el campus desde la entrada
     * principal hasta las escaleras de la cafetería.
     */
    public function run(): void
    {
        // Limpiar tablas previas
        DB::table('conexiones')->delete();
        DB::table('nodos')->delete();

        // Obtener IDs de tipos de nodos
        $tipoPaso = DB::table('nodo_tipos')->where('nombre', 'pasillo')->value('id');
        $tipoEscalera = DB::table('nodo_tipos')->where('nombre', 'escaleras')->value('id');

        // Array de 51 nodos con (id, latitud, longitud, nombre, tipo_id, piso)
        $nodos = [
            [1, 4.1540264, -74.8956435, 'Entrada Principal', $tipoPaso, 1],
            [2, 4.1540938, -74.8957258, 'Paso 2', $tipoPaso, 1],
            [3, 4.1541906, -74.8957731, 'Paso 3', $tipoPaso, 1],
            // ... más nodos omitidos por brevedad ...
            [51, 4.1569111, -74.8976424, 'Escalera Cafetería', $tipoEscalera, 1],
        ];

        // Insertar cada nodo
        foreach ($nodos as $nodo) {
            DB::table('nodos')->insert([
                'id' => $nodo[0],
                'latitud' => $nodo[1],
                'longitud' => $nodo[2],
                'nombre' => $nodo[3],
                'tipo_id' => $nodo[4],
                'piso' => $nodo[5],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Crear conexiones secuenciales (cada nodo conecta con el siguiente)
        for ($i = 1; $i < 51; $i++) {
            $n1 = DB::table('nodos')->where('id', $i)->first();
            $n2 = DB::table('nodos')->where('id', $i + 1)->first();
            
            if ($n1 && $n2) {
                // Calcular distancia Haversine entre dos puntos GPS
                $distancia = $this->calcularDistancia(
                    $n1->latitud, $n1->longitud, 
                    $n2->latitud, $n2->longitud
                );
                
                // Insertar conexión bidireccional
                DB::table('conexiones')->insert([
                    'nodo_origen_id' => $i,
                    'nodo_destino_id' => $i + 1,
                    'distancia' => $distancia,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Calcular distancia entre dos puntos GPS (Fórmula Haversine)
     */
    private function calcularDistancia($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371; // Radio terrestre en km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distancia = $R * $c * 1000; // En metros
        
        return round($distancia, 2);
    }
}
```

---

### NodoSeeder - Bloque D (Alternativo)

**Ubicación:** `database/seeders/NodoSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nodo;

class NodoSeeder extends Seeder
{
    /**
     * Seed nodos de Bloque D
     * 
     * Usa Factory en lugar de inserciones directas.
     * Es más flexible y reutilizable.
     */
    public function run(): void
    {
        // Limpiar tabla
        \Illuminate\Support\Facades\DB::table('conexiones')->delete();
        \Illuminate\Support\Facades\DB::table('nodos')->delete();

        // Crear nodos específicos
        $salon101 = Nodo::create([
            'nombre' => 'Salón 101',
            'latitud' => 4.14800000,
            'longitud' => -74.88500000,
            'tipo_id' => 1,
            'piso' => 1,
        ]);

        $banos = Nodo::create([
            'nombre' => 'Baños Bloque D',
            'latitud' => 4.14800100,
            'longitud' => -74.88500000,
            'tipo_id' => 2,
            'piso' => 1,
        ]);

        // Crear conexiones bidireccionales
        $salon101->vecinos()->attach($banos->id, ['distancia' => 8.5]);
        $banos->vecinos()->attach($salon101->id, ['distancia' => 8.5]);
    }
}
```

---

## 🚀 Cómo Ejecutar los Seeders

### Ejecutar todos los seeders

```bash
# Ejecutar DatabaseSeeder (ejecuta todos los seeders llamados)
php artisan db:seed

# Output:
# Seeding: Database\Seeders\CampusItfipSeeder
# Seeded:  Database\Seeders\CampusItfipSeeder
```

### Ejecutar un seeder específico

```bash
# Solo CampusItfipSeeder
php artisan db:seed --class=CampusItfipSeeder

# Solo NodoSeeder
php artisan db:seed --class=NodoSeeder
```

### Fresh + Seed (Resetar BD y repoblar)

```bash
# Elimina todas las tablas, recrea migraciones Y ejecuta seeders
php artisan migrate:fresh --seed

# Output:
# Dropping all tables...
# Rolling back: 2026_03_05_000000_add_soft_deletes_to_users
# Rolled back: 2026_03_05_000000_add_soft_deletes_to_users
# ...
# Migrating: 2026_03_05_000000_add_soft_deletes_to_users
# Migrated: 2026_03_05_000000_add_soft_deletes_to_users
# ...
# Seeding: Database\Seeders\DatabaseSeeder
# Seeded: Database\Seeders\DatabaseSeeder
```

---

## 🧪 Usando Factory en Tests

Las factories son especialmente útiles en tests:

```php
// tests/Feature/RegisterTest.php

use App\Models\User;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    public function test_existing_user_cannot_register_again()
    {
        // Crear usuario con Factory
        $user = User::factory()->create([
            'email' => 'juan@example.com'
        ]);

        // Intentar registrar con mismo email
        $response = $this->postJson('/api/register', [
            'nombres' => 'Juan',
            'apellidos' => 'Pérez',
            'email' => 'juan@example.com',  // ← Ya existe
            'password' => 'test123456',
            'password_confirmation' => 'test123456',
        ]);

        // Esperar error 422
        $response->assertStatus(422);
    }

    public function test_create_multiple_users_with_factory()
    {
        // Crear 10 usuarios aleatorios
        $users = User::factory(10)->create();

        // Cada uno tiene email único (por .unique())
        $this->assertCount(10, $users);
        $this->assertEquals(10, User::count());
    }

    public function test_unverified_user_cannot_login()
    {
        // Crear usuario sin email verificado
        $user = User::factory()
            ->unverified()
            ->create([
                'email' => 'test@example.com',
                'password' => Hash::make('password'),
            ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Debes verificar tu correo']);
    }
}
```

---

## 📊 Datos Generados vs Datos Reales

### Con Factory + Faker

```php
// Generar 5 usuarios aleatorios
User::factory(5)->create();

// Resultado ejemplo:
[
  {
    "id": 1,
    "name": "Dr. Carroll Jenkins",
    "email": "carroll.jenkins@example.com",
    "created_at": "2026-03-26 14:30:45"
  },
  {
    "id": 2,
    "name": "Mary Walker",
    "email": "mary.walker@example.com",
    "created_at": "2026-03-26 14:30:46"
  },
  // ...
]
```

### Con Seeder (Datos predefinidos)

```php
// CampusItfipSeeder inserta 51 nodos específicos del campus
$nodos = [
    [1, 4.1540264, -74.8956435, 'Entrada Principal', 1, 1],
    [2, 4.1540938, -74.8957258, 'Paso 2', 1, 1],
    // ... coordenadas exactas del campus ITFIP
];

// Resultado:
[
  {
    "id": 1,
    "nombre": "Entrada Principal",
    "latitud": 4.1540264,
    "longitud": -74.8956435,
    "tipo_id": 1,
    "piso": 1
  },
  // ... datos reales del campus
]
```

---

## 🔄 Cuándo Usar Factory vs Seeder

| Caso | Usar | Razón |
|------|------|-------|
| Generar usuarios aleatorios para testing | **Factory** | Datos ficticios, rápido |
| Crear datos específicos del dominio (nodos campus) | **Seeder** | Datos reales, predefinidos |
| Test: crear 100 usuarios | **Factory** | Genera automáticos |
| Test: verificar usuario específico | **Seeder** | Control total |
| Development local | **Seeder + Factory** | Ambos conjuntamente |

---

---

# 5. FLUJO DE INTEGRACIÓN COMPLETO

## 🔗 Ejemplo Real: Registro de Usuario

Este diagrama muestra cómo todas las implementaciones trabajan juntas:

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. USUARIO FRONTEND (Vue.js)                                    │
│    Completa formulario de registro:                             │
│    - Nombres: "Juan Pedro"                                       │
│    - Apellidos: "García López"                                   │
│    - Email: "juan@example.com"                                   │
│    - Password: "segura123456"                                    │
│    - Confirmation: "segura123456"                                │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────────┐
│ 2. ENVÍA POST /api/register CON DATOS                           │
│    Content-Type: application/json                               │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────────┐
│ 3. LARAVEL RECEPCIONA (routes/api.php)                          │
│    Route::post('/register', [RegisterController@register])      │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────────┐
│ 4. LARAVEL INYECTA FORMREQUEST                                  │
│    public function register(RegisterRequest $request)           │
│                             ^^^^^^^^^^^^^^^^^^                  │
│    Laravel detecta: "registrar necesita RegisterRequest"        │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────────┐
│ 5. REGISTERREQUEST VALIDA AUTOMÁTICAMENTE                       │
│    ✓ nombres: es string, máx 191, solo letras                  │
│    ✓ apellidos: es string, máx 191, solo letras                │
│    ✓ email: formato válido de email                            │
│    ✓ password: mínimo 8 caracteres                             │
│    ✓ password_confirmation: coincide con password              │
└────────────────┬────────────────────────────────────────────────┘
                 │
            ¿Válido?
            /       \
           /         \
          NO         SÍ
          │           │
          ↓           ↓
    [422 Error]   [Continúa]
                      │
                      ↓
┌─────────────────────────────────────────────────────────────────┐
│ 6. CONTROLADOR SOLO EJECUTA LÓGICA DE NEGOCIO                  │
│    public function register(RegisterRequest $request)           │
│    {                                                             │
│        $validated = $request->validated();  ← Datos seguros    │
│                                                                  │
│        // Crear registro pendiente                              │
│        $pending = PendingUser::create([                         │
│            'nombres' => $validated['nombres'],                  │
│            'apellidos' => $validated['apellidos'],              │
│            'email' => $validated['email'],                      │
│            'password' => Hash::make($validated['password']),    │
│        ]);                                                       │
│                                                                  │
│        // Enviar email de verificación                          │
│        $this->emailService->sendVerification(...);             │
│    }                                                             │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────────┐
│ 7. RETORNA RESPUESTA JSON                                       │
│    {                                                             │
│      "message": "Usuario registrado. Verifica tu correo.",     │
│      "pending_id": 42,                                          │
│      "email": "juan@example.com"                                │
│    }                                                             │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────────┐
│ 8. VUE.JS RECIBE RESPUESTA                                      │
│    - Muestra mensaje "Revisa tu email"                          │
│    - Redirige a pantalla de verificación                        │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔗 Ejemplo Real: Búsqueda de Nodos

```
┌─────────────────────────────────────────────────────────────────┐
│ 1. USUARIO ESCRIBE EN BUSCADOR (Frontend Vue)                   │
│    Input: "Salón"                                               │
│    Evento: @input → handleSearch()                              │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────────┐
│ 2. FETCH A API                                                   │
│    GET /api/search/nodos?q=Salón&tipo_id=1&per_page=20         │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────────┐
│ 3. LARAVEL INTERCEPTA (routes/api.php)                          │
│    Route::get('/search/nodos', [SearchController@searchNodos])  │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────────┐
│ 4. SEARCHCONTROLLER INYECTA FILTERREQUEST                       │
│    public function searchNodos(FilterNodoRequest $request)      │
│                                 ^^^^^^^^^^^^^^^^^              │
│    Laravel valida parámetros de búsqueda automáticamente       │
└────────────────┬────────────────────────────────────────────────┘
                 │
            ¿Válido?
            /       \
           /         \
          NO         SÍ
          │           │
          ↓           ↓
    [422 Error]   [Continúa]
                      │
                      ↓
┌─────────────────────────────────────────────────────────────────┐
│ 5. EJECUTA BÚSQUEDA EN BD                                       │
│    $query = Nodo::query();                                      │
│    $query->where('nombre', 'like', '%Salón%');                │
│    $query->where('tipo_id', 1);                                │
│    $results = $query->paginate(20);                            │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────────┐
│ 6. RETORNA JSON PAGINADO                                        │
│    {                                                             │
│      "data": [                                                   │
│        {"id": 101, "nombre": "Salón 101", ...},                │
│        {"id": 102, "nombre": "Salón 102", ...}                 │
│      ],                                                          │
│      "links": {...},                                            │
│      "meta": {                                                   │
│        "current_page": 1,                                        │
│        "total": 2,                                               │
│        "per_page": 20                                            │
│      }                                                           │
│    }                                                             │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────────┐
│ 7. VUE.JS ACTUALIZA VISTA                                       │
│    - Muestra 2 resultados encontrados                           │
│    - Con paginación (1 de 1 página)                             │
│    - Usuario ve lista de nodos que coinciden                    │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔗 Ejemplo Real: Actualizar Perfil con Cambio Opcional de Password

```
┌─────────────────────────────────────────────────────────────────┐
│ USUARIO ELIGE: ACTUALIZAR PERFIL SIN CAMBIAR PASSWORD           │
│                                                                  │
│ PUT /api/users/42                                               │
│ {                                                                │
│   "nombres": "Juan Carlo",         ← Cambio                    │
│   "email": "juanc@example.com",   ← Cambio                    │
│   // ❌ NO envía password                                        │
│ }                                                                │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────────┐
│ UPDATEUSERREQUEST VALIDA                                        │
│ 'nombres' => 'sometimes|string|...'                            │
│ 'email' => 'sometimes|email|...'                               │
│ 'password' => 'sometimes|string|min:8|confirmed'  ← OPTIONAL  │
│                                                                  │
│ ✓ nombres: presente → valida                                    │
│ ✓ email: presente → valida                                      │
│ ✓ password: AUSENTE → se IGNORA (sometimes)                    │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────────┐
│ CONTROLADOR ACTUALIZA SELECTIVAMENTE                            │
│ $user->nombres = 'Juan Carlo';                                  │
│ $user->email = 'juanc@example.com';                            │
│ // Password NO se actualiza ← Mantiene contraseña anterior     │
│ $user->save();                                                   │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────────┐
│ RETORNA: Perfil actualizado exitosamente                       │
│ Password se mantiene igual (no fue enviado)                     │
└─────────────────────────────────────────────────────────────────┘

════════════════════════════════════════════════════════════════════

┌─────────────────────────────────────────────────────────────────┐
│ USUARIO ELIGE: ACTUALIZAR PERFIL CON NUEVO PASSWORD             │
│                                                                  │
│ PUT /api/users/42                                               │
│ {                                                                │
│   "nombres": "Juan Carlo",                                      │
│   "email": "juanc@example.com",                                │
│   "password": "nuevaSegura2026",       ← NUEVO                 │
│   "password_confirmation": "nuevaSegura2026"  ← CONFIRMACIÓN   │
│ }                                                                │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────────┐
│ UPDATEUSERREQUEST VALIDA                                        │
│ ✓ nombres: presente → valida                                    │
│ ✓ email: presente → valida                                      │
│ ✓ password: PRESENTE → validar:                                │
│   - ¿Mínimo 8 caracteres? ✓ SÍ (18 caracteres)                │
│   - ¿Coincide confirmation? ✓ SÍ                                │
│ → PASS                                                           │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────────┐
│ CONTROLADOR ACTUALIZA TODO                                      │
│ $user->nombres = 'Juan Carlo';                                  │
│ $user->email = 'juanc@example.com';                            │
│ $user->password = Hash::make('nuevaSegura2026');  ← NUEVA      │
│ $user->save();                                                   │
└────────────────┬────────────────────────────────────────────────┘
                 │
                 ↓
┌─────────────────────────────────────────────────────────────────┐
│ RETORNA: Perfil actualizado exitosamente                       │
│ Nombres, email Y password se actualizaron                       │
└─────────────────────────────────────────────────────────────────┘
```

---

---

# 6. CASOS DE USO Y EJEMPLOS COMBINADOS

## 📋 Tabla de Referencia: ¿Cuál FormRequest Usar?

| Acción | URL | Método | FormRequest | Route |
|--------|-----|--------|-------------|-------|
| Registro | /api/register | POST | RegisterRequest | web.php |
| Login | /api/login | POST | LoginRequest | web.php |
| Verificar Email | /api/verify-email | POST | VerifyEmailRequest | web.php |
| Enviar Código Reset | /api/password/send-code | POST | SendPasswordCodeRequest | web.php |
| Verificar Código Reset | /api/password/verify-code | POST | VerifyPasswordCodeRequest | web.php |
| Resetear Password | /api/password/reset | POST | ResetPasswordRequest | web.php |
| Actualizar Perfil | /api/users/{id} | PUT | UpdateUserRequest | api.php |
| Crear Nodo | /api/nodos | POST | StoreNodoRequest | api.php |
| Conectar Nodos | /api/nodos/connect | POST | ConectarNodoRequest | api.php |
| Buscar Nodos | /api/search/nodos | GET | FilterNodoRequest | api.php |
| Buscar Usuarios | /api/search/usuarios | GET | FilterUserRequest | api.php |

---

## 🔍 Tabla de Referencia: Reglas Según Contexto

| Campo | POST Register | PUT Update | POST Reset | Nota |
|-------|---|---|---|---|
| nombres | required | sometimes | - | Obligatorio solo en alta |
| email | required | sometimes | - | Opcional en actualización |
| password | required | sometimes | required | Siempre obligatorio en reset, opcional en update |
| tipo_id (nodo) | required | - | - | Siempre obligatorio |
| bidireccional (conexión) | - | - | sometimes | Opcional en conexiones |

---

## 🎯 Checklist: Cómo Integrar una Nueva Acción

### Paso 1: Crear FormRequest

```bash
php artisan make:request MiNuevaAccionRequest
```

Ubicación: `app/Http/Requests/MiNuevaAccionRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MiNuevaAccionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // O validar permisos
    }

    public function rules(): array
    {
        return [
            'campo1' => 'required|string',
            'campo2' => 'sometimes|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'campo1.required' => 'Campo1 es obligatorio',
        ];
    }
}
```

### Paso 2: Usar en Controlador

```php
use App\Http\Requests\MiNuevaAccionRequest;

public function miNuevaAccion(MiNuevaAccionRequest $request)
{
    $validated = $request->validated();
    
    // Tu lógica aquí
    
    return response()->json(['success' => true]);
}
```

### Paso 3: Agregar Ruta

```php
// routes/api.php
Route::post('/mi-nueva-accion', 
    [\App\Http\Controllers\MiControlador::class, 'miNuevaAccion']
);
```

### ¡Listo! ✅

Laravel automáticamente:
- ✓ Valida los parámetros
- ✓ Retorna 422 si falla
- ✓ Pasa el request al método si pasa

---

---

# CONCLUSIONES

## 📊 Resumen de Beneficios Implementados

| Implementación | Beneficio | Impacto |
|---|---|---|
| **Form Requests** | Separación de responsabilidades | Código limpio, mantenible |
| **Validación Automática** | Laravel maneja errores | Menos código boilerplate |
| **Mensajes Personalizados** | Errores en español claro | Mejor UX |
| **FilterRequest + SearchController** | Búsqueda profesional | API moderna, escalable |
| **Regla Sometimes** | Validaciones condicionales | Flexibilidad en updates |
| **Faker + Seeders** | Datos de prueba realistas | Testing más confiable |

---

## 🎓 Mejores Prácticas Aplicadas

1. **DRY (Don't Repeat Yourself)**
   - Validaciones: En FormRequest, no repetidas en controlador
   - Mensajes: Centralizados en `messages()`

2. **SOLID - Single Responsibility**
   - Controlador: Solo lógica de negocio
   - FormRequest: Solo validaciones
   - SearchController: Solo búsqueda

3. **Seguridad**
   - Validaciones robustas (regex, tipos, longitud)
   - Íconos seguros (exists, unique)
   - Autorización (authorize method)

4. **Testing**
   - Fácil mockear FormRequests
   - Factories para datos reproducibles
   - Seeders para BD consistente

5. **Documentación**
   - Comentarios explicativos en código
   - Mensajes de error en español
   - Ejemplos de uso claro

---

## 📚 Archivos Clave del Proyecto

```
app/Http/
├── Requests/              ← 11 FormRequests
│   ├── RegisterRequest.php
│   ├── LoginRequest.php
│   ├── UpdateUserRequest.php (con sometimes)
│   ├── FilterNodoRequest.php
│   └── ... (8 más)
│
├── Controllers/Api/
│   ├── RegisterController.php
│   ├── LoginController.php
│   ├── PasswordResetController.php
│   ├── NodoController.php
│   └── SearchController.php ← Búsqueda/Filtros

database/
├── factories/
│   └── UserFactory.php  ← Genera usuarios aleatorios
│
└── seeders/
    ├── DatabaseSeeder.php
    ├── CampusItfipSeeder.php  ← 51 nodos del campus
    └── NodoSeeder.php  ← Nodos de Bloque D
```

---

## 🚀 Próximos Pasos Recomendados

1. **Implementar Autorización**
   ```php
   public function authorize(): bool
   {
       return $this->user()->is_admin; // Validar rol
   }
   ```

2. **Agregar Tests Completos**
   ```bash
   php artisan make:test RegisterTest --feature
   php artisan make:test SearchTest --feature
   ```

3. **Documentar API** (OpenAPI/Swagger)
   - Documentar endpoints
   - Especificar parámetros
   - Ejemplos de respuesta

4. **Implementar Rate Limiting**
   - Limitar búsquedas por usuario
   - Prevenir brute force en login

5. **Cachear Resultados de Búsqueda**
   - Redis para búsquedas frecuentes
   - Invalidar cache en inserciones

---

## 📞 Preguntas Frecuentes

### P: ¿Puedo modificar las reglas de validación?
**R:** Sí, edita la función `rules()` en el FormRequest correspondiente.

### P: ¿Cómo agregar validaciones personalizadas?
**R:** Crea una custom rule o usa `Rule::` para reglas complejas.

### P: ¿Se puede usar esto en producción?
**R:** Sí, es arquitectura recomendada para producción. Laravel es robusto.

### P: ¿Cómo testeo un FormRequest?
**R:** Puedes hacer POST/GET al endpoint y verificar respuestas.

### P: ¿Puedo cambiar mensajes de error?
**R:** Sí, modifica `messages()` en cada FormRequest.

---

## 📝 Referencias Oficiales

- [Laravel Form Requests](https://laravel.com/docs/10.x/validation#creating-form-requests)
- [Laravel Validation Rules](https://laravel.com/docs/10.x/validation#available-validation-rules)
- [Laravel Factories](https://laravel.com/docs/10.x/eloquent-factories)
- [Laravel Seeders](https://laravel.com/docs/10.x/seeding)
- [Faker Library](https://github.com/fzaninotto/Faker)

---

## 📄 Documento Historia

| Versión | Fecha | Cambios |
|---------|-------|---------|
| 1.0 | 2026-03-26 | Documento inicial con 4 implementaciones |

---

**Documento preparado para: Equipo de Desarrollo ITFIP Maps**  
**Formato: Markdown (compatible con GitHub, GitLab, VS Code)**  
**Última actualización: 26 de marzo de 2026**

---

*Este documento debe ser leído por todo el equipo de desarrollo antes de trabajar en el proyecto.*
