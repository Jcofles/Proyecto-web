# Configuración de Túneles Cloudflare para ITFIP Maps

## Problema
Cuando usas túneles de Cloudflare, los enlaces de verificación de email apuntan a `localhost` en lugar de la URL del túnel, causando el error "No se puede acceder a este sitio".

## Solución

### 1. Iniciar los túneles (en terminales separadas)

**Terminal 1 - Backend Laravel:**
```bash
cd Clase1
cloudflared tunnel --url http://localhost:8000
```
Copia la URL generada, ejemplo: `https://dock-lucas-propose-pro.trycloudflare.com`

**Terminal 2 - Frontend Vue:**
```bash
cd itfip-map
cloudflared tunnel --url http://localhost:5173
```
Copia la URL generada, ejemplo: `https://glance-autumn-apache-impose.trycloudflare.com`

### 2. Actualizar archivo .env del backend

Edita `Clase1/.env` y actualiza estas líneas:

```env
# Cuando uses túneles, comenta localhost y descomenta las URLs del túnel:
# APP_URL=http://localhost:8000
APP_URL=https://dock-lucas-propose-pro.trycloudflare.com

# APP_FRONTEND_URL=http://localhost:5173
APP_FRONTEND_URL=https://glance-autumn-apache-impose.trycloudflare.com

# También actualiza SANCTUM_STATEFUL_DOMAINS
SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173,*.trycloudflare.com
```

### 3. Actualizar archivo .env del frontend

Edita `itfip-map/.env` y actualiza:

```env
# VITE_API_URL=http://localhost:8000/api
VITE_API_URL=https://dock-lucas-propose-pro.trycloudflare.com/api
```

### 4. Reiniciar servicios

**Backend:**
```bash
# Detener el servidor (Ctrl+C)
# Limpiar caché
php artisan config:clear
php artisan cache:clear

# Reiniciar
php artisan serve
```

**Frontend:**
```bash
# Detener Vite (Ctrl+C)
# Reiniciar
npm run dev
```

### 5. Probar el registro

1. Ve a la URL del túnel del frontend: `https://glance-autumn-apache-impose.trycloudflare.com`
2. Regístrate con un email real
3. El enlace de verificación ahora apuntará correctamente al frontend del túnel
4. Al hacer clic, te redirigirá a: `https://glance-autumn-apache-impose.trycloudflare.com/verify-email?token=...`

## Notas importantes

- ⚠️ Las URLs de Cloudflare cambian cada vez que reinicias el túnel
- 🔄 Debes actualizar el `.env` cada vez que generes nuevos túneles
- 📱 Usa las URLs del túnel para probar desde tu celular
- 🏠 Para desarrollo local sin túnel, vuelve a comentar las líneas del túnel

## Verificación rápida

Después de actualizar el `.env`, verifica que esté correcto:

```bash
cd Clase1
php artisan tinker
```

Luego ejecuta:
```php
echo config('app.frontend_url');
// Debe mostrar: https://glance-autumn-apache-impose.trycloudflare.com
exit
```

## Troubleshooting

**Error: "No se puede acceder a este sitio"**
- ✅ Verifica que `APP_FRONTEND_URL` en `.env` tenga la URL del túnel del frontend
- ✅ Ejecuta `php artisan config:clear` después de cambiar `.env`
- ✅ Reinicia el servidor Laravel

**Error: CORS**
- ✅ Verifica que `SANCTUM_STATEFUL_DOMAINS` incluya `*.trycloudflare.com`
- ✅ El middleware CORS ya está configurado en `app/Http/Middleware/Cors.php`

**Email no llega**
- ✅ Verifica la configuración SMTP en `.env`
- ✅ Revisa los logs: `storage/logs/laravel.log`
