# Configuración de Gmail SMTP para Laravel - Pasos Completados

## ✅ Cambios Realizados

### 1. **Eliminado `Mail::failures()`** (ERROR PRINCIPAL RESUELTO)
**Archivo:** `Clase1/app/Services/EmailService.php`
- ❌ Removido: `if (!empty(Mail::failures())) { Log::error(...) }`
- ✅ Retenido: `try-catch` para capturar excepciones de envío

El motivo: `Mail::failures()` no existe en Laravel 9+ (usas 10+). Ahora los errores son capturados directamente por las excepciones.

---

### 2. **Configuración de .env actualizada**
**Archivo:** `Clase1/.env`

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password-de-16-digitos
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 🔑 PASO CRÍTICO: Obtener la Contraseña de Aplicación de Gmail

> **Nota:** No puedes usar tu contraseña normal de Gmail. Necesitas generar una contraseña de aplicación específica.

### Pasos:

1. Ve a **https://myaccount.google.com**
2. En el menú izquierdo → **Seguridad**
3. Busca **"Verificación en dos pasos"**: 
   - Si NO está activada → actívala primero (es obligatorio)
4. Cuando 2FA esté activo, aparecerá la opción **"Contraseñas de aplicaciones"** 
5. Haz clic en "Contraseñas de aplicaciones"
6. Selecciona:
   - **Aplicación:** Correo (Mail)
   - **Dispositivo:** Otro dispositivo → escribe "Laravel Local"
7. Genera contraseña → **copia los 16 caracteres** (sin espacios)
8. Pega en el `.env`:
   ```dotenv
   MAIL_PASSWORD=abcdefghijklmnop
   ```

---

## 🧹 PASO IMPORTANTE: Limpiar Cachés

Después de cambiar `.env`, DEBES ejecutar estos comandos en la carpeta `Clase1/`:

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

**Opcional pero recomendado:**
```bash
php artisan config:cache
```

---

## 🧪 PRUEBA MANUAL: Verificar que Gmail Funciona

En la carpeta `Clase1/`, abre Tinker:

```bash
php artisan tinker
```

Luego pega esto (cambia `tu-email@gmail.com` por tu Gmail real):

```php
Mail::raw('Este es un correo de prueba desde Laravel local con Gmail SMTP', function($message) {
    $message->to('tu-email@gmail.com')->subject('Prueba Gmail SMTP');
});
```

Presiona Enter.

**Resultado esperado:**
- If devolvió `null` o no dio error → ✅ **Correo enviado**
- Revisa tu Gmail (incluyendo la carpeta de Spam/No Deseado)
- Si llega → **¡Gmail funciona!**

Si sale error:
- **"Connection timed out"** → Firewall de Windows bloquea puerto 587
  - Solución: Abre Windows Security → Firewall → Reglas de salida → permite puerto 587
- **"Authentication failed"** → App Password incorrecta o 2FA no activado
- **"EHLO command failed"** → Problema de conexión, intenta esperar 30 segundos y reintentar

---

## 🔄 Flujo de Registro Ahora:

1. Usuario envía formulario de registro
2. Laravel crea el usuario en BD o tabla `pending_users`
3. **Gmail recibe el correo** con el link de verificación
4. Usuario hace clic en el link → se verifica la cuenta
5. Frontend Vue conecta al backend para confirmar verificación

---

## 📝 Resumen de Cambios

| Cambio | Antes | Después |
|--------|-------|---------|
| **Mail driver** | `log` | `smtp` |
| **Manejo de errores** | `Mail::failures()` (🔴 no existe) | `try-catch` (✅ funciona) |
| **Email real** | ❌ Solo en logs | ✅ Enviado a Gmail |
| **MAIL_HOST** | `127.0.0.1` | `smtp.gmail.com` |

---

## ⚠️ Si Algo Aún Falla

1. Verifica el log: `Clase1/storage/logs/laravel.log`
2. Busca la línea más reciente que diga `ERROR`
3. Usa `php artisan tinker` para confirmar que Mail funciona
4. Asegúrate de que la App Password tiene exactamente 16 caracteres sin espacios

---

**Creado:** 2 de marzo de 2026
**Estado:** Listo para usar
