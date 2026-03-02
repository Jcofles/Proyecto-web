# Sistema de Registro con Confirmación de Correo - ITFIP Maps

## 📋 Descripción General
Se ha implementado un sistema completo de registro de usuarios con confirmación de correo electrónico. El flujo es:

1. Usuario llena el formulario de registro
2. Backend valida y crea el usuario en estado **NO confirmado**
3. Se envía un correo con enlace de confirmación (válido 24 horas)
4. Usuario hace clic en el enlace desde su correo
5. Frontend verifica el token y confirma la cuenta
6. Usuario es redirigido al MapView

---

## 🔧 Archivos Modificados/Creados

### Backend (Laravel)

#### 1. **Controlador de Registro** 
- Archivo: `app/Http/Controllers/Api/RegisterController.php`
- Funciones:
  - `register()`: Crea usuario + envía correo de verificación
  - `verifyEmail()`: Confirma email con token
  - `resendVerification()`: Reenvía correo si expiró

#### 2. **Migración de Base de Datos**
- Archivo: `database/migrations/2026_03_01_000000_add_email_verification_to_users.php`
- Añade campos a tabla `users`:
  - `email_verified_at`: timestamp de confirmación
  - `email_verification_token`: token único (64 caracteres)
  - `email_verification_expires_at`: expiración del token

#### 3. **Modelo User**
- Archivo: `app/Models/User.php`
- Actualizado `$fillable` con los campos de verificación

#### 4. **Rutas API**
- Archivo: `routes/api.php`
- Nuevos endpoints:
  ```
  POST /api/auth/register
  POST /api/auth/verify-email
  POST /api/auth/resend-verification
  ```

#### 5. **Controlador de Verificación (opcional)**
- Archivo: `app/Http/Controllers/Api/EmailVerificationController.php`
- Permite redirigir desde URL de correo web

---

### Frontend (Vue 3)

#### 1. **Servicio de API**
- Archivo: `src/services/api.js`
- Métodos:
  - `auth.register(userData)`: Envía datos de registro
  - `auth.verifyEmail(token)`: Verifica email
  - `auth.resendVerification(email)`: Reenvía correo

#### 2. **Vista de Registro**
- Archivo: `src/views/auth/RegisterView.vue`
- Estados:
  - `'form'`: Muestra formulario de registro
  - `'verification'`: Muestra pantalla de espera
- Funcionalidades:
  - Validación en tiempo real
  - Envío de datos al backend
  - Manejo de errores
  - Botón para reenviar correo

#### 3. **Vista de Verificación de Email**
- Archivo: `src/views/auth/VerifyEmailView.vue`
- Funcionalidades:
  - Verifica token de URL
  - Muestra estado (cargando/éxito/error)
  - Redirige a MapView tras 3 segundos
  - Botón "Ir ahora" para ir inmediatamente

#### 4. **Router actualizado**
- Archivo: `src/router/index.js`
- Nueva ruta: `/verify-email?token=xxx`

#### 5. **Variables de Entorno**
- Archivo: `.env`
- Variable: `VITE_API_URL=http://localhost:8000/api`

---

## 🚀 Instrucciones de Instalación

### Backend (Laravel)

1. **Actualizar la base de datos:**
   ```bash
   cd Clase1
   php artisan migrate
   ```

2. **Configurar el correo:**
   - En `.env`, cambiar de `MAIL_MAILER=log` a tu proveedor (ej: `MAIL_MAILER=smtp`)
   - Requiere configuración en `.env`:
     ```
     MAIL_HOST=smtp.gmail.com
     MAIL_PORT=587
     MAIL_USERNAME=tu-email@gmail.com
     MAIL_PASSWORD=tu-contraseña
     MAIL_ENCRYPTION=tls
     MAIL_FROM_ADDRESS=noreply@itfipmaps.local
     MAIL_FROM_NAME="ITFIP Maps"
     ```

3. **Iniciar servidor:**
   ```bash
   php artisan serve
   ```

### Frontend (Vue)

1. **Instalar dependencias:**
   ```bash
   cd itfip-map
   npm install
   ```

2. **Iniciar servidor de desarrollo:**
   ```bash
   npm run dev
   ```

---

## 📊 Flujo de Datos

```
┌─────────────────┐
│    Usuario      │
│   Completa      │
│   Formulario    │
└────────┬────────┘
         │
         ▼
┌─────────────────────────────┐
│  RegisterView.vue           │
│  - Valida datos             │
│  - Envía a /auth/register   │
└────────┬────────────────────┘
         │
         ▼
┌─────────────────────────────┐
│  RegisterController         │
│  - Crea usuario (NO verificado)
│  - Genera token (24h)       │
│  - Envía correo             │
└────────┬────────────────────┘
         │
         ▼
┌─────────────────────────────┐
│  Vue muestra               │
│  "Revisa tu correo"        │
└────────┬────────────────────┘
         │
         ▼
┌─────────────────────────────┐
│  Usuario hace clic          │
│  en enlace del correo       │
└────────┬────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│  VerifyEmailView.vue         │
│  - Lee token de URL          │
│  - Envía a /auth/verify-email
└────────┬─────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│  RegisterController::verify  │
│  - Valida token              │
│  - Confirma email            │
│  - Guarda fecha de confirmación
└────────┬─────────────────────┘
         │
         ▼
┌──────────────────────────────┐
│  Vue redirige a              │
│  MapView (✓ Registrado)      │
└──────────────────────────────┘
```

---

## 🔐 Seguridad

- **Tokens únicos**: Cada registro genera un token aleatorio de 64 caracteres
- **Expiración**: Tokens válidos solo 24 horas
- **Encriptación de contraseñas**: Se usa `bcrypt` con saltos configurables
- **Validación de email**: Verifica que sea email válido y único
- **Validación de nombres**: Solo acepta letras y espacios

---

## 📧 Configuración de Correo

### Opción 1: Gmail (Recomendado para desarrollo)

1. Habilitar "Acceso a aplicaciones menos seguras"
2. En `.env`:
   ```
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=tu-email@gmail.com
   MAIL_PASSWORD=contraseña-app
   MAIL_ENCRYPTION=tls
   ```

### Opción 2: Mailtrap (Sandbox)

1. Crear cuenta en https://mailtrap.io
2. Copiar configuración SMTP
3. Pegar en `.env`

### Opción 3: Desarrollo local

1. Mantener `MAIL_MAILER=log`
2. Ver correos en `storage/logs/laravel.log`

---

## 🧪 Pruebas

### Prueba 1: Registro básico
1. Ir a http://localhost:5173/register
2. Llenar formulario
3. Hacer clic en "Crear cuenta"
4. Debería mostrarse "Revisa tu correo"

### Prueba 2: Verificación de email
1. Buscar el correo enviado (o revisar logs)
2. Hacer clic en el enlace o copiar token
3. Ir a http://localhost:5173/verify-email?token=XXXX
4. Debería mostrar "¡Correo verificado!"
5. Redirige a MapView

### Prueba 3: Email incorrecto
1. Usar dirección de correo que ya existe
2. Debería mostrar error "Email ya registrado"

### Prueba 4: Reenviar correo
1. En pantalla de verificación, hacer clic "Reenviar correo"
2. Se envía nuevo correo

---

## 🐛 Troubleshooting

| Problema | Solución |
|----------|----------|
| "Error al conectar con API" | Verificar que Laravel está en `localhost:8000` |
| "Error al enviar correo" | Revisar configuración MAIL_* en `.env` |
| "Token expirado" | Reenviar correo (válido 24 horas) |
| "Email ya registrado" | Usar otro email o resetear BD |
| CORS error | Asegurar que `APP_URL` en Laravel es correcto |

---

## 📝 Notas Importantes

1. **Variables de entorno**: Asegurar que `.env` en ambos proyectos está configurado correctamente
2. **Base de datos**: Ejecutar migrations después de actualizar el código
3. **Correos en ambiente local**: Se guardan en `storage/logs/` si MAIL_MAILER=log
4. **Token en URL**: Es seguro porque es de un uso único y expira en 24h
5. **Contraseñas**: Se envían por HTTPS en producción (sin cambios necesarios)

---

## 📞 Contacto/Soporte

Para preguntas sobre la implementación, revisar:
- Logs de Laravel: `Clase1/storage/logs/laravel.log`
- Consola del navegador: F12 > Console
- Respuestas de API: Network tab en DevTools
