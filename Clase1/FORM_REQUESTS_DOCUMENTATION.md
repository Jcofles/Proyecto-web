# Validaciones Profesionales - Form Requests

## 📋 Implementación en tu proyecto

Se han implementado **Form Requests** para todas las validaciones de tu API REST. Esto reemplaza los `Validator::make()` manuales con una arquitectura más profesional y mantenible.

### ✅ Form Requests Creados

#### Autenticación & Registro
- **GenerateRequest** → Validaciones para registro
- **LoginRequest** → Validaciones para login
- **VerifyEmailRequest** → Validaciones para verificación de email
- **SendPasswordCodeRequest** → Validaciones para enviar código de recuperación
- **VerifyPasswordCodeRequest** → Validaciones para verificar código
- **ResetPasswordRequest** → Validaciones para resetear contraseña

#### Actualización de Usuarios
- **UpdateUserRequest** → Para actualizar datos de usuario (usa regla `sometimes`)

#### Nodos
- **StoreNodoRequest** → Para crear nuevos nodos
- **ConectarNodoRequest** → Para conectar nodos

#### Filtros & Búsqueda
- **FilterNodoRequest** → Para filtrar nodos en tiempo real
- **FilterUserRequest** → Para filtrar usuarios en tiempo real

---

## 🔄 Cambios en Controladores

Todos los controladores han sido actualizados para usar Form Requests automáticamente:

### Antes (Manual):
```php
public function login(Request $request)
{
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }
    
    $email = $request->email;
    // ...
}
```

### Después (Form Request):
```php
public function login(LoginRequest $request)
{
    $validated = $request->validated();
    $email = $validated['email'];
    // ...
}
```

**Ventajas:**
- ✅ Validación automática (Laravel retorna 422 si falla)
- ✅ Mensajes personalizados
- ✅ Código más limpio y mantenible
- ✅ Reutilizable en múltiples controladores
- ✅ Fácil de testear

---

## 📌 Regla `sometimes` para Contraseñas

Implementada en `UpdateUserRequest.php`:

```php
'password' => 'sometimes|string|min:8|confirmed',
'password_confirmation' => 'sometimes|string|min:8',
```

**¿Qué es `sometimes`?**
- Solo valida el campo SI existe en la request
- Perfecto para actualizaciones donde password es OPCIONAL
- En registro, password sigue siendo `required`

**Casos de uso:**
```
POST /register       → password: required ✓ (RegisterRequest)
PUT /users/{id}      → password: sometimes ✓ (UpdateUserRequest)
PATCH /users/{id}    → password: sometimes ✓ (UpdateUserRequest)
```

---

## 🔍 Búsqueda en Tiempo Real - Filtros API

Aunque **Livewire NO es apropiado** para tu arquitectura (estás usando API REST + Vue.js), se han creado **Form Requests de filtro** que tu frontend Vue puede usar:

### 1. Filtrar Nodos

```php
// FilterNodoRequest.php
'q' => 'sometimes|string|max:255',      // Búsqueda por nombre
'tipo_id' => 'sometimes|integer|exists:nodo_tipos,id',
'piso' => 'sometimes|integer',
'per_page' => 'sometimes|integer|min:1|max:100',
```

**Ejemplo de endpoint (puedes implementar en NodoController):**
```php
public function search(FilterNodoRequest $request)
{
    $filters = $request->validated();
    
    $query = Nodo::query();
    
    if ($filters['q'] ?? null) {
        $query->where('nombre', 'like', '%' . $filters['q'] . '%');
    }
    
    if ($filters['tipo_id'] ?? null) {
        $query->where('tipo_id', $filters['tipo_id']);
    }
    
    if ($filters['piso'] ?? null) {
        $query->where('piso', $filters['piso']);
    }
    
    $perPage = $filters['per_page'] ?? 15;
    
    return response()->json($query->paginate($perPage));
}
```

### 2. Filtrar Usuarios

```php
// FilterUserRequest.php
'q' => 'sometimes|string|max:255',      // Búsqueda por nombre/email
'status' => 'sometimes|string|in:activo,inactivo,bloqueado,eliminado',
'per_page' => 'sometimes|integer|min:1|max:100',
```

---

## 🚀 Cómo Usar en el Frontend Vue

Para búsqueda en tiempo real desde Vue.js:

```javascript
// En tu composable o servicio de API
async searchNodos(query, filters = {}) {
  const params = new URLSearchParams({
    q: query,
    ...filters,
    per_page: 20
  })
  
  const response = await fetch(`/api/nodos/search?${params}`)
  return response.json()
}

// En tu componente Vue
watch(searchQuery, async (newQuery) => {
  if (newQuery.length > 2) {
    const results = await searchNodos(newQuery)
    this.resultNodos = results.data
  }
})
```

---

## ❌ ¿Por qué NO instalé Livewire?

1. **Tu arquitectura es API REST + SPA Vue.js**
   - Livewire es para Blade (server-side rendering)
   - No funciona bien con APIs REST

2. **Ya tienes una mejor alternativa**
   - Vue.js en el frontend hace búsqueda en tiempo real
   - Laravel API en el backend responde con JSON
   - Combina lo mejor de ambos mundos

3. **Si NECESITARAS Livewire**, sería:
   - `composer require livewire/livewire`
   - Cambiar toda tu arquitectura a Blade
   - Dejar de usar Vue.js

**Recomendación:** Usa los FilterRequest para implementar endpoints de búsqueda que tu Vue.js pueda consumir.

---

## 📁 Archivos Modificados

### Controladores Actualizados:
- ✅ `app/Http/Controllers/Api/RegisterController.php`
- ✅ `app/Http/Controllers/Api/LoginController.php`
- ✅ `app/Http/Controllers/Api/PasswordResetController.php`
- ✅ `app/Http/Controllers/Api/NodoController.php`

### Form Requests Creados:
- ✅ `app/Http/Requests/RegisterRequest.php`
- ✅ `app/Http/Requests/LoginRequest.php`
- ✅ `app/Http/Requests/VerifyEmailRequest.php`
- ✅ `app/Http/Requests/SendPasswordCodeRequest.php`
- ✅ `app/Http/Requests/VerifyPasswordCodeRequest.php`
- ✅ `app/Http/Requests/ResetPasswordRequest.php`
- ✅ `app/Http/Requests/UpdateUserRequest.php` (con `sometimes`)
- ✅ `app/Http/Requests/StoreNodoRequest.php`
- ✅ `app/Http/Requests/ConectarNodoRequest.php`
- ✅ `app/Http/Requests/FilterNodoRequest.php`
- ✅ `app/Http/Requests/FilterUserRequest.php`

---

## 🧪 Probando los Form Requests

Los Form Requests se validan automáticamente. Si la validación falla, Laravel responde con 422:

```bash
# Ejemplo: falta email
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"password": "123456"}'

# Respuesta: 422 con errores de validación
```

---

## ✨ Resumen

| Feature | Estado | Detalles |
|---------|--------|----------|
| Form Requests | ✅ IMPLEMENTADO | 11 Form Requests en `app/Http/Requests/` |
| Regla `sometimes` | ✅ IMPLEMENTADO | En `UpdateUserRequest` para contraseña opcional |
| Filtros API | ✅ IMPLEMENTADO | `FilterNodoRequest`, `FilterUserRequest` |
| Livewire | ❌ NO APROPIADO | API REST + Vue.js es mejor alternativa |
| Faker & Seeder | ✅ YA EXISTÍA | `UserFactory`, `CampusItfipSeeder`, `NodoSeeder` |

**Sin cambios en funcionalidad existente** ✓
