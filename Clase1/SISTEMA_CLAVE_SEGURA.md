# 🔐 Sistema de Clave Segura (.jw) - Documentación Completa

## 📋 Resumen de Implementación

Se ha implementado exitosamente un sistema de recuperación de cuenta mediante archivo seguro `.jw` con las siguientes características:

### ✅ Funcionalidades Implementadas

1. **Generación Automática de Archivo Seguro**
   - Al registrarse y verificar el email, se genera automáticamente un hash único
   - El archivo contiene 128 líneas de código encriptado único por usuario
   - Basado en email principal + email seguro + APP_KEY

2. **Campo de Correo Seguro Obligatorio**
   - Agregado en el formulario de registro
   - Validación: debe ser diferente al correo principal
   - Se usa para enviar el archivo .jw en caso de pérdida

3. **Banner de Descarga en Dashboard**
   - Aparece solo si el usuario NO ha descargado el archivo
   - Diseño atractivo con animaciones
   - Botón de descarga directa
   - Se oculta automáticamente después de descargar

4. **Interfaz de Login con Clave Segura**
   - Botón "Ingresar con clave segura" en LoginView
   - Vista dedicada SecureKeyView con diseño coherente
   - Permite cargar archivo .jw desde el dispositivo
   - Opción de solicitar envío al correo seguro

5. **Notificación de Cambio de Contraseña**
   - Toast estilo Android al ingresar con clave segura
   - Sugiere cambiar la contraseña
   - Funcional con botones de acción

---

## 📁 Archivos Creados/Modificados

### Backend (Laravel)

#### Nuevos Archivos:
```
app/Http/Controllers/Api/SecureKeyController.php
app/Services/SecureKeyService.php
```

#### Archivos Modificados:
```
app/Http/Controllers/Api/RegisterController.php
app/Models/User.php
app/Models/PendingUser.php
database/migrations/2026_04_09_000000_add_secure_recovery_to_users_and_pending_users.php
routes/api.php
```

### Frontend (Vue.js)

#### Nuevos Archivos:
```
src/views/auth/SecureKeyView.vue
```

#### Archivos Modificados:
```
src/views/auth/LoginView.vue
src/views/auth/RegisterView.vue
src/views/DashboardView.vue
src/services/api.js
src/router/index.js (ya tenía la ruta)
```

---

## 🔧 Estructura del Archivo .jw

### Nombre del Archivo
```
para ti crack.jw
```

### Contenido (128 líneas)
```
001|A1B2C3D4E5F6...
002|F6E5D4C3B2A1...
003|1A2B3C4D5E6F...
...
128|Z9Y8X7W6V5U4...
```

### Generación
```php
// SecureKeyService.php
public function makeSecureKeyFileContent(string $email, string $secureEmail): string
{
    $seed = $this->buildSeed($email, $secureEmail);
    $lines = [];

    for ($i = 0; $i < 128; $i++) {
        $hash = strtoupper(hash_hmac('sha256', "$seed|line:$i", $seed));
        $lines[] = sprintf('%03d|%s', $i + 1, $hash);
    }

    return implode("\n", $lines);
}
```

---

## 🔐 Seguridad

### Hash Único por Usuario
```php
$seed = hash_hmac('sha256', $email . '|' . $secureEmail, config('app.key'));
$fileHash = hash('sha256', $fileContent);
```

### Almacenamiento en BD
```sql
-- Tabla users
secure_email VARCHAR(191)
secure_key_hash VARCHAR(128)  -- Hash del archivo completo
secure_key_generated_at TIMESTAMP
secure_key_downloaded_at TIMESTAMP
```

### Validación en Login
```php
$expectedHash = $this->secureKeyService->buildSecureKeyHash($user->email, $user->secure_email);
$providedHash = hash('sha256', $request->secure_key_content);

if ($expectedHash !== $providedHash) {
    return response()->json(['message' => 'Archivo inválido'], 401);
}
```

---

## 🎨 Diseño UI/UX

### SecureKeyView
- Fondo animado con rutas y nodos (coherente con LoginView)
- Canvas con efecto de escaneo radar
- Tema día/noche sincronizado
- Reloj en tiempo real
- Animaciones suaves

### Banner en Dashboard
- Gradiente verde (#10b981 → #059669)
- Icono de llave
- Texto explicativo
- Botón de descarga prominente
- Animación de entrada/salida

### Toast de Android
- Posición fija inferior centrada
- Fondo oscuro con borde cyan
- Iconos y texto claro
- Botones de acción funcionales
- Auto-oculta después de 7 segundos

---

## 📡 Endpoints API

### POST /api/auth/register
```json
{
  "nombres": "Juan",
  "apellidos": "Pérez",
  "email": "juan@example.com",
  "secure_email": "juan.seguro@example.com",
  "password": "segura123",
  "password_confirmation": "segura123"
}
```

### POST /api/auth/login-with-key
```json
{
  "email": "juan@example.com",
  "secure_key_content": "001|A1B2C3...\n002|F6E5D4..."
}
```

### POST /api/auth/send-secure-key-email
```json
{
  "email": "juan@example.com"
}
```
**Respuesta:** Envía el archivo .jw al correo seguro registrado

### GET /api/auth/secure-key-download
**Headers:** `Authorization: Bearer {token}`  
**Respuesta:** Descarga directa del archivo `para ti crack.jw`

---

## 🚀 Flujo de Usuario

### 1. Registro
```
Usuario → Formulario Registro
  ├─ Nombres
  ├─ Apellidos
  ├─ Email
  ├─ Email Seguro (NUEVO)
  ├─ Contraseña
  └─ Confirmar Contraseña
       ↓
Backend genera secure_key_hash
       ↓
Email de verificación enviado
       ↓
Usuario verifica email
       ↓
Cuenta activada + Clave segura lista
```

### 2. Dashboard (Primera vez)
```
Usuario ingresa al Dashboard
       ↓
Banner verde aparece:
"🔐 Clave Segura Generada
Descarga tu archivo de recuperación..."
       ↓
Usuario hace clic en "Descargar Ahora"
       ↓
Archivo "para ti crack.jw" descargado
       ↓
Banner desaparece automáticamente
```

### 3. Recuperación con Clave Segura
```
Usuario olvida contraseña
       ↓
Click en "Ingresar con clave segura"
       ↓
SecureKeyView carga
       ↓
Usuario ingresa email
       ↓
Usuario selecciona archivo .jw
       ↓
Backend valida hash
       ↓
Login exitoso
       ↓
Toast aparece:
"Has ingresado con clave segura.
¿Quieres cambiar tu contraseña?"
       ↓
Usuario puede cambiar contraseña o descartar
```

### 4. Solicitar Archivo por Email
```
Usuario no tiene archivo .jw
       ↓
Click en "Enviar al correo seguro"
       ↓
Ingresa email de cuenta
       ↓
Backend envía archivo al secure_email
       ↓
Usuario recibe email con adjunto
       ↓
Descarga y usa archivo .jw
```

---

## 🧪 Testing

### Probar Registro
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "nombres": "Test",
    "apellidos": "User",
    "email": "test@example.com",
    "secure_email": "test.secure@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### Probar Descarga
```bash
curl -X GET http://localhost:8000/api/auth/secure-key-download \
  -H "Authorization: Bearer {token}" \
  --output "para ti crack.jw"
```

### Probar Login con Clave
```bash
curl -X POST http://localhost:8000/api/auth/login-with-key \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "secure_key_content": "001|ABC...\n002|DEF..."
  }'
```

---

## 🎯 Características Destacadas

### ✅ Seguridad
- Hash único basado en 3 factores (email + secure_email + APP_KEY)
- 128 líneas de código encriptado
- Validación en servidor
- No se almacena el archivo completo, solo el hash

### ✅ UX/UI
- Diseño coherente con el resto de la aplicación
- Animaciones suaves y profesionales
- Tema día/noche sincronizado
- Mensajes claros y concisos
- Flujo intuitivo

### ✅ Funcionalidad
- Generación automática al verificar email
- Descarga directa desde dashboard
- Login alternativo sin contraseña
- Envío por email en caso de pérdida
- Notificación para cambiar contraseña

### ✅ Mantenibilidad
- Código organizado en servicios
- Form Requests para validación
- Controlador dedicado
- Documentación completa
- Sin daños al código existente

---

## 📝 Notas Importantes

1. **El archivo .jw es único por usuario**
   - No se puede usar el archivo de otro usuario
   - Basado en email + secure_email + APP_KEY

2. **El banner solo aparece si NO se ha descargado**
   - Se verifica `secure_key_downloaded_at` en BD
   - Se oculta automáticamente al descargar

3. **El toast solo aparece al ingresar con .jw**
   - Se detecta con `localStorage.getItem('secureKeyLogin')`
   - Se limpia automáticamente después de mostrar

4. **El correo seguro es obligatorio**
   - Validado en RegisterRequest
   - Debe ser diferente al email principal
   - Se usa para envío de emergencia

---

## 🔄 Migración de Base de Datos

```bash
# Ejecutar migración
php artisan migrate

# Verificar columnas agregadas
php artisan tinker
>>> DB::table('users')->first()
>>> DB::table('pending_users')->first()
```

---

## ✨ Resumen Final

**Sistema completamente funcional y profesional** que permite:
- ✅ Registro con correo seguro obligatorio
- ✅ Generación automática de archivo .jw
- ✅ Banner de descarga en dashboard
- ✅ Login alternativo con archivo .jw
- ✅ Envío por email en emergencias
- ✅ Notificación para cambiar contraseña
- ✅ Diseño coherente y atractivo
- ✅ Sin daños al código existente

**Todo organizado, bonito y funcional** ✨
