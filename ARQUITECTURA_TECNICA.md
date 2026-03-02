# 🏗️ Arquitectura del Sistema de Registro - DOCUMENTACIÓN TÉCNICA

## 📋 Resumen General

Sistema de registro con **verificación de email basada en tokens** para ITFIP Maps.

En esta versión los datos del solicitante **no se escriben en la tabla `users` hasta que
confirma su correo**. Mientras tanto permanecen en una tabla temporal `pending_users`
que guarda nombre, email y contraseña hasheada junto con un token de validación.

```
Usuario → Vue Form → API Laravel → Email → Token URL → Verificación → Crear usuario → MapView
   |          |             |          |         |            |            |
Formulario  Submit     Validar     Save    64-char     Verificar     Move to users
           Campos      pending   (pending) Email     24h exp                 
```

---

## 🔄 Flujo Completo

### 1️⃣ Usuario Llena Formulario (Frontend)
**Archivo**: `itfip-map/src/views/auth/RegisterView.vue`

```javascript
// Stage 1: Mostrar formulario
<template v-if="stage === 'form'">
  <form @submit.prevent="handleRegister">
    <input v-model="form.nombres" placeholder="ex: Juan" />
    <input v-model="form.apellidos" placeholder="ex: Pérez" />
    <input v-model="form.email" type="email" />
    <input v-model="form.password" type="password" />
    <input v-model="form.password_confirmation" type="password" />
    <button type="submit">Crear cuenta</button>
  </form>
</template>
```

**Validaciones Frontend**:
- Nombres: Solo letras + espacios (regex: `/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/`)
- Email: Validación HTML5
- Contraseña: Mín 8 caracteres, indicador de fortaleza (5 niveles)
- Confirmación: Debe coincidir exactamente con contraseña

### 2️⃣ Submit → Llamar API (Frontend)
**Archivo**: `itfip-map/src/services/api.js`

```javascript
// api.js
export const auth = {
  register: async (data) => {
    const response = await fetch(
      `${import.meta.env.VITE_API_URL}/auth/register`,
      {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      }
    );
    
    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message);
    }
    
    return response.json();
  },
  
  verifyEmail: async (token) => {
    // POST /api/auth/verify-email
  },
  
  resendVerification: async (email) => {
    // POST /api/auth/resend-verification
  }
};
```

**Variables de Entorno**:
```
VITE_API_URL=http://localhost:8000/api  (itfip-map/.env)
```

### 3️⃣ API Recibe y Valida (Backend)
**Archivo**: `Clase1/app/Http/Controllers/Api/RegisterController.php`

El registro ya no escribe en `users`; en su lugar guarda el dato en `pending_users`
junto con un token que caduca en 24 h. Sólo cuando el usuario hace clic en el link
se crea la fila definitiva.

```php
public function register(Request $request)
{
  // 1. VALIDAR
  $validator = Validator::make($request->all(), [
    'nombres' => 'required|string|max:50|regex:/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/iu',
    'apellidos' => 'required|string|max:50|regex:/^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s]+$/iu',
    // nota: validamos unicidad contra `pending_users` porque aún no hay usuario
    'email' => 'required|string|email|unique:pending_users,email',
    'password' => 'required|string|min:8|confirmed',
    'password_confirmation' => 'required|string|same:password',
  ]);

  // 2. SI FALLA VALIDACIÓN
  if ($validator->fails()) {
    return response()->json([
      'message' => 'Validación fallida',
      'errors' => $validator->messages(),
    ], 422);
  }

  // 3. PREPARAR DATOS VALIDADOS
  $validated = $validator->validated();

  // 4. GUARDAR EN TABLA "PENDING"
  $pending = PendingUser::create([
      'nombres' => $validated['nombres'],
      'apellidos' => $validated['apellidos'],
      'email' => $validated['email'],
      'password' => Hash::make($validated['password']),
      'email_verification_token' => Str::random(64),
      'email_verification_expires_at' => Carbon::now()->addHours(24),
  ]);

  // 5. ENVIAR EMAIL
  $this->sendVerificationEmail($pending);

  // 6. RESPONDER AL CLIENTE
  return response()->json([

    'message' => 'Usuario registrado. Verifica tu correo electrónico.',
    'user_id' => $user->id,
    'email' => $user->email,
  ], 201);
}
```

**Cambios Clave Realizados**:
- ✅ Cambio de `$request->validate()` → `Validator::make()` para mejor control
- ✅ Adicionado `'password_confirmation' => 'required|string|same:password'` (validación explícita)
- ✅ Token generado en `User::create()` (operación atómica, no dos pasos)
- ✅ Logging detallado de errores en `catch` block

### 4️⃣ Enviar Email de Verificación (Backend)
En lugar de implementar la lógica dentro del controlador, ahora hay **un servicio
específico** que se encarga de cualquier envío de correos. Esto hace que el
código sea más reutilizable y testeable.

- **Clase de servicio**: `App/Services/EmailService.php`, con método
  `sendVerification($entity, $token)`.
- **Mailable**: `App/Mail/VerificationEmail.php` genera el contenido
  usando la plantilla `resources/views/emails/verification.blade.php`.

El controlador inyecta el servicio y lo utiliza así:

```php
$this->emailService->sendVerification(
    $pending,
    $token
);
```

El mailable se encarga de componer el asunto, remitente y el enlace de
verificación. La plantilla HTML incluye el botón y el enlace directo, y es
fácil de personalizar sin tocar el controlador.

**Configuración de Email** (en `.env` se mantiene idéntica):
```
MAIL_MAILER=log        # solo logs en desarrollo
# cambiar a smtp y añadir credenciales válidas para enviar correos reales
```

El correo (cuando se usa el driver `log`) continúa escribiéndose en
`Clase1/storage/logs/laravel.log`.

**Configuración de Email** (en `.env`):
```
# Desarrollo (default - solo logs)
MAIL_MAILER=log

# Producción (usar uno de estos)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=app-password
MAIL_ENCRYPTION=tls
```

Email se guarda en: `Clase1/storage/logs/laravel.log`

### 5️⃣ Usuario Hace Clic en Email (Frontend)
**Archivo**: `itfip-map/src/views/auth/VerifyEmailView.vue`

```javascript
beforeMount() {
  const token = this.$route.query.token;
  
  if (!token) {
    this.state = 'error';
    return;
  }

  this.verifyEmail(token);
}

async verifyEmail(token) {
  try {
    this.state = 'loading';
    
    await auth.verifyEmail({ token });
    
    this.state = 'success';
    
    // Redirigir después de 3 segundos
    setTimeout(() => {
      this.$router.push('/map');
    }, 3000);
    
  } catch (error) {
    this.state = 'error';
    this.error = error.message;
  }
}
```

**URL de Verificación**:
```
http://localhost:5174/verify-email?token=abcd1234...
```

### 6️⃣ Verificar Token en Backend (Backend)
**Archivo**: `Clase1/app/Http/Controllers/Api/RegisterController.php`

```php
public function verifyEmail(Request $request)
{
  // 1. VALIDAR QUE TIENE TOKEN
  $request->validate([
    'token' => 'required|string|size:64',
  ]);

  // 2. BUSCAR USUARIO CON ESTE TOKEN
  $user = User::where('email_verification_token', $request->token)
              ->first();

  // 3. VERIFICAR QUE EXISTE Y NO HA EXPIRADO
  if (!$user) {
    return response()->json([
      'message' => 'Token inválido',
    ], 404);
  }

  if (Carbon::now()->isAfter($user->email_verification_expires_at)) {
    return response()->json([
      'message' => 'Token expirado. Solicita un nuevo email.',
    ], 410);
  }

  // 4. MARCAR COMO VERIFICADO
  $user->update([
    'email_verified_at' => Carbon::now(),
    'email_verification_token' => null,
    'email_verification_expires_at' => null,
  ]);

  return response()->json([
    'message' => 'Email verificado exitosamente',
    'user' => $user,
  ], 200);
}
```

### 7️⃣ Redirigir a MapView (Frontend)
**Archivo**: `itfip-map/src/router/index.js`

```javascript
const routes = [
  // ... otras rutas
  {
    path: '/verify-email',
    component: VerifyEmailView,
  },
  {
    path: '/map',
    component: MapView,
  },
];
```

---

## 🗄️ Estructura de Base de Datos

### Tabla `users` (expandida)
```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    
    -- Email Verification Fields
    email_verified_at TIMESTAMP NULL,
    email_verification_token VARCHAR(64) UNIQUE NULL,
    email_verification_expires_at TIMESTAMP NULL,
    
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEXES:
    - UNIQUE(email)
    - UNIQUE(email_verification_token)
);
```

### Tabla `cache` (para caché)
```
Existe desde Laravel default - no modificada
```

### Tabla `jobs` (para colas - no usado aquí)
```
Existe desde Laravel default - no modificada
```

---

## 🔐 Seguridad Implementada

| Aspecto | Implementación |
|--------|----------------|
| **Tokens** | 64 caracteres aleatorios (`Str::random(64)`) |
| **Expiración** | 24 horas desde creación |
| **Contraseña** | Hash bcrypt (default Laravel) |
| **Emails únicos** | Índice UNIQUE en DB + validación |
| **SQL Injection** | Prepared statements (Eloquent) |
| **XSS** | Vue escapa output automáticamente |
| **CSRF** | Laravel CSRF (si se integra login) |

---

## 📊 Respuestas API

### Register: Exitoso (201)
```json
{
  "message": "Usuario registrado. Verifica tu correo electrónico.",
  "user_id": 1,
  "email": "juan@example.com"
}
```

### Register: Error Validación (422)
```json
{
  "message": "Validación fallida",
  "errors": {
    "email": ["The email has already been taken"],
    "password_confirmation": ["The password_confirmation and password must match"]
  }
}
```

### Verify Email: Exitoso (200)
```json
{
  "message": "Email verificado exitosamente",
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "juan@example.com",
    "email_verified_at": "2026-03-05T10:30:00Z",
    "created_at": "2026-03-05T10:15:00Z"
  }
}
```

### Verify Email: Token Inválido (404)
```json
{
  "message": "Token inválido"
}
```

### Verify Email: Token Expirado (410)
```json
{
  "message": "Token expirado. Solicita un nuevo email."
}
```

---

## 🔧 Cambios Principales Realizados

### Problema 1: Validación de contraseña fallaba
**Causa**: Uso de regla `confirmed` sin manejo de errores adecuado

**Solución**:
```php
// ANTES (incorrecto)
$validated = $request->validate([
    'password' => 'required|string|min:8|confirmed',
]);

// DESPUÉS (correcto)
$validator = Validator::make($request->all(), [
    'password' => 'required|string|min:8',
    'password_confirmation' => 'required|string|same:password',
]);
```

### Problema 2: Usuario parcialmente creado
**Causa**: Crear usuario en dos pasos (create + update)

**Solución**:
```php
// ANTES (dos operaciones)
$user = User::create([ /* sin token */ ]);
$user->update(['email_verification_token' => $token]);

// DESPUÉS (operación atómica)
$user = User::create([
    // ... otros campos
    'email_verification_token' => Str::random(64),
    'email_verification_expires_at' => Carbon::now()->addHours(24),
]);
```

### Problema 3: Base de datos contaminada
**Causa**: Intento de rollback con restricciones FK

**Solución**:
```bash
# ANTES (fallaba)
php artisan migrate:rollback

# DESPUÉS (exitoso)
php artisan migrate:fresh  # Drop todo y recrear
```

---

## 📁 Estructura de Archivos

```
Proyecto-web/
├── Clase1/                          # Laravel Backend
│   ├── .env                         # Config de entorno
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/
│   │   │       └── Api/
│   │   │           └── RegisterController.php  ⭐ MAIN
│   │   └── Models/
│   │       └── User.php             ⭐ MODIFIED
│   ├── database/
│   │   ├── migrations/
│   │   │   └── 2026_03_01_000000_add_email_verification_to_users.php  ⭐ NEW
│   │   └── seeders/
│   │       └── DatabaseSeeder.php
│   ├── routes/
│   │   └── api.php                  ⭐ MODIFIED
│   └── storage/
│       └── logs/
│           └── laravel.log          (emails se guardan aquí en dev)
│
├── itfip-map/                       # Vue Frontend
│   ├── .env                         # Config de entorno
│   ├── src/
│   │   ├── services/
│   │   │   └── api.js               ⭐ NEW
│   │   ├── views/
│   │   │   └── auth/
│   │   │       ├── RegisterView.vue ⭐ MODIFIED
│   │   │       └── VerifyEmailView.vue ⭐ NEW
│   │   └── router/
│   │       └── index.js             ⭐ MODIFIED
│   └── vite.config.js
│
├── PRUEBA_REGISTRO.md               ⭐ THIS FILE - instrucciones de prueba
└── verificar_estado.bat             ⭐ NEW - script de diagnóstico
```

---

## 🔄 Estados del Usuario

```
1. NUEVO
   └── Completa formulario
   └── Datos guardados en DB
   └── email_verified_at = NULL
   └── email_verification_token = "abc123..."
   └── email_verification_expires_at = NOW + 24h

2. VERIFICANDO
   └── Usuario recibe email
   └── Hace clic en link
   └── Frontend valida token
   └── Backend actualiza registro

3. VERIFICADO
   └── email_verified_at = NOW
   └── email_verification_token = NULL
   └── email_verification_expires_at = NULL
   └── Puede acceder a /map

4. TOKEN EXPIRADO
   └── email_verified_at = NULL
   └── email_verification_expires_at < NOW
   └── Debe hacer clic en "Reenviar correo"
```

---

## 🧪 Test Manual

```bash
# 1. Ver logs en vivo
tail -f Clase1/storage/logs/laravel.log | grep -E "Mailed|Email"

# 2. Probar endpoint con curl
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "nombres": "Juan",
    "apellidos": "Pérez",
    "email": "juan@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# 3. Verificar usuario en DB
php artisan tinker
>>> User::latest()->first()
>>> User::where('email', 'juan@example.com')->first()
```

---

## 📝 Notas Finales

- Todos los cambios fueron realizados en operación atómica (no hay estados intermedios)
- El sistema es stateless en el frontend (sin sesiones PHP)
- Token-based verification es más seguro que URL directas
- Email verification es OPCIONAL en el flujo (usuario puede registrarse pero no verificado)
- Para producción: configurar SMTP real, agregar rate limiting, implementar resend email throttling
