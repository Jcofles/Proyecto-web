# ITFIP Maps - Guía de Inicio Rápido

## 🚀 Desarrollo Local (Recomendado)

### Opción 1: Script Automático
```bash
# Ejecuta este archivo:
iniciar_proyecto.bat
```

Esto iniciará:
- Backend Laravel en `http://localhost:8000`
- Frontend Vue en `http://localhost:5173`

Luego abre tu navegador en: **http://localhost:5173**

---

### Opción 2: Manual

**Terminal 1 - Backend:**
```bash
cd Clase1
php artisan serve
```

**Terminal 2 - Frontend:**
```bash
cd itfip-map
npm run dev
```

---

## 🌐 Con Túneles Cloudflare (Para acceso externo)

### Paso 1: Iniciar túneles
```bash
iniciar_tuneles.bat
```

### Paso 2: Copiar URLs
Busca en las ventanas de Cloudflare las URLs generadas:
- **Laravel**: `https://xxx-xxx.trycloudflare.com` (puerto 8000)
- **Vite**: `https://yyy-yyy.trycloudflare.com` (puerto 5173)

### Paso 3: Actualizar configuración
Edita `itfip-map\.env`:
```env
VITE_API_URL=https://tu-url-laravel.trycloudflare.com/api
```

### Paso 4: Reiniciar Vite
En la terminal de Vite:
- Presiona `Ctrl+C`
- Ejecuta: `npm run dev`

### Paso 5: Acceder
Abre la URL de Vite en tu navegador.

---

## ❌ Problemas Comunes

### "Failed to execute 'json' on 'Response': Unexpected end of JSON input"
**Causa**: El backend Laravel no está respondiendo correctamente.

**Solución**:
1. **Verifica que Laravel esté corriendo**:
   ```bash
   cd Clase1
   php artisan serve
   ```
   Deberías ver: `Server running on [http://127.0.0.1:8000]`

2. **Prueba la API manualmente**:
   - Abre: http://localhost:8000/api/nodos
   - Deberías ver JSON (aunque sea vacío: `{"data":[]}`)
   - Si ves HTML o error 404, hay un problema con las rutas

3. **Ejecuta el diagnóstico**:
   ```bash
   diagnostico.bat
   ```

4. **Verifica la configuración del frontend**:
   - Archivo: `itfip-map\.env`
   - Debe contener: `VITE_API_URL=http://localhost:8000/api`
   - Si lo cambias, reinicia Vite (Ctrl+C y `npm run dev`)

5. **Limpia la caché de Laravel**:
   ```bash
   cd Clase1
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   ```

### "No puedo registrar usuarios"
**Causa**: El frontend no se conecta al backend.

**Solución**:
1. Verifica que ambos servidores estén corriendo
2. Revisa `itfip-map\.env` tenga la URL correcta:
   - Local: `VITE_API_URL=http://localhost:8000/api`
   - Túnel: `VITE_API_URL=https://tu-url.trycloudflare.com/api`
3. Reinicia el servidor de Vite

### "No puedo iniciar sesión con mi usuario"
**Causa**: El usuario no ha verificado su email.

**Solución**:
1. Revisa tu correo (incluyendo spam)
2. Haz clic en el enlace de verificación
3. Si no llegó, usa "Reenviar correo" en la pantalla de verificación

### "Error de conexión"
**Causa**: El backend no está corriendo.

**Solución**:
```bash
cd Clase1
php artisan serve
```

---

## 📧 Configuración de Email

El proyecto está configurado para enviar emails reales vía Gmail SMTP.

Para cambiar la configuración, edita `Clase1\.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-contraseña-de-aplicacion
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME="ITFIP Maps"
```

---

## 🗄️ Base de Datos

**Nombre**: `mapeo_itfip`

**Migraciones**:
```bash
cd Clase1
php artisan migrate
```

**Resetear base de datos**:
```bash
php artisan migrate:fresh
```

---

## 📝 Estructura del Proyecto

```
Proyecto-web/
├── Clase1/              # Backend Laravel
│   ├── app/
│   ├── routes/
│   └── .env
├── itfip-map/           # Frontend Vue
│   ├── src/
│   ├── public/
│   └── .env
└── iniciar_proyecto.bat # Script de inicio
```
