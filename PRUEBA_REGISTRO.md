# 🧪 Prueba del Sistema de Registro - GUÍA RÁPIDA

## ✅ Estado Actual

- ✓ Laravel está corriendo en `http://localhost:8000`
- ✓ Vue está corriendo en `http://localhost:5174`
- ✓ Base de datos fue reiniciada (`migrate:fresh`)
- ✓ Todas las tablas creadas correctamente
- ✓ RegisterController mejorado con mejor manejo de errores

## 🚀 Cómo Probar

### Paso 1: Abrir la aplicación Vue
```
http://localhost:5174/register
```

### Paso 2: Llenar el formulario
```
Nombres: Juan
Apellidos: Pérez
Email: juan@example.com
Contraseña: Password123!
Confirmar: Password123!
```

### Paso 3: Hacer clic en "Crear cuenta"

#### Si todo funciona:
- ✓ Debería mostrar un spinner de carga
- ✓ Luego mostrar "Revisa tu correo"
- ✓ Mostrar el email ingresado
- ✓ Envío de correo se realiza mediante un servicio dedicado (`EmailService`) y
      un mailable (`VerificationEmail`). Esto facilita verificar con `php artisan test`
      usando `Mail::fake()`.

#### Si hay error:
- Ver la consola del navegador (F12 > Console)
- Ver los logs de Laravel: `Clase1/storage/logs/laravel.log`

---

## 📧 Ver el Correo de Verificación

Hay 3 opciones:

### Opción 1: Logs de Laravel (MÁS FÁCIL PARA DESARROLLO)
```
tail -f Clase1/storage/logs/laravel.log
```
Buscar la URL de verificación que contiene el token.

### Opción 2: Usar Mailtrap
1. Crear cuenta en https://mailtrap.io
2. Copiar SMTP credentials a `.env`

### Opción 3: Usar Gmail
1. En `.env` de Laravel:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=contraseña-app (no tu contraseña real)
MAIL_ENCRYPTION=tls
```

---

## 🔍 Troubleshooting

### "Error al registrar usuario"
1. Revisar F12 > Console (qué error específico)
2. Revisar `Clase1/storage/logs/laravel.log`
3. Verificar que el email no está duplicado

### "Email has already been taken"
- El email ya fue guardado en la base de datos de pendientes o ya existe un
  usuario verificado con ese correo.
- Solución: usar otro email, o bien borrar el registro pendiente/usuario y
  volver a ejecutar `php artisan migrate:fresh` para limpieza.

### "CORS Error" o "Failed to fetch"
- Laravel no está corriendo
- Verificar que está en http://localhost:8000
- El .env tiene URL incorrecta

### "Verificación fallida" después de hacer clic en correo
- El token puede estar mal
- El token puede haber expirado (válido 24h)
- Revisar logs de Laravel

---

## 📝 API Endpoints Disponibles

### POST /api/auth/register
```json
{
  "nombres": "Juan",
  "apellidos": "Pérez",
  "email": "juan@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

Respuesta exitosa (201):
```json
{
  "message": "Usuario registrado. Verifica tu correo electrónico.",
  "pending_id": 1,
  "email": "juan@example.com"
}
```

> Nota: el registro aún queda en la tabla `pending_users`. Solo cuando el correo
> se confirme se creará un usuario definitivo en la tabla `users`.


### POST /api/auth/verify-email
```json
{
  "token": "xxxxxxxxxxxxx..."
}
```

Respuesta exitosa (200):
```json
{
  "message": "Email verificado exitosamente",
  "user": { ... }
}
```

### POST /api/auth/resend-verification
```json
{
  "email": "juan@example.com"
}
```

---

## 💾 Base de Datos

Tabla `users` ahora tiene:
- `id` - ID del usuario
- `name` - Nombre completo
- `email` - Email único
- `password` - Hash de contraseña
- `email_verified_at` - Fecha de verificación (NULL = no verificado)
- `email_verification_token` - Token de 64 caracteres
- `email_verification_expires_at` - Expira en 24 horas
- `remember_token` - Para "remember me"
- `created_at`, `updated_at` - Timestamps

---

## 🔐 Seguridad Implementada

✓ Tokens aleatorios de 64 caracteres
✓ Expiración de 24 horas
✓ Contraseñas hasheadas con bcrypt
✓ Validación de email (único)
✓ Validación de nombres (solo letras)
✓ Validación de contraseña (mín 8 caracteres)
✓ Coincidencia de confirmación de contraseña

---

## 📞 Próximos Pasos

1. Una vez verificado el email → Debería redirigir a `/map`
2. Falta implementar login en `/login`
3. Falta proteger rutas que requieran autenticación
4. Falta guardar token de sesión en cliente

---

## 🚨 IMPORTANTE

El archivo viejo `RegisterView_new.vue` fue eliminado automáticamente.
Si tienes dudas, revisar:

- Frontend: `itfip-map/src/views/auth/RegisterView.vue`
- Backend: `Clase1/app/Http/Controllers/Api/RegisterController.php`
- Rutas API: `Clase1/routes/api.php`
- Modelo: `Clase1/app/Models/User.php`
