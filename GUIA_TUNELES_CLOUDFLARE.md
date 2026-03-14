# Guía de Uso de Túneles Cloudflare

## Inicio Rápido

### Opción 1: Iniciar todo automáticamente
Ejecuta el archivo `iniciar_tuneles.bat` en la raíz del proyecto. Esto iniciará:
- Laravel en puerto 8000
- Vite en puerto 5173
- Túnel de Cloudflare para Laravel
- Túnel de Cloudflare para Vite

### Opción 2: Iniciar manualmente
1. Inicia Laravel: `cd Clase1 && php artisan serve`
2. Inicia Vite: `cd Clase1 && npm run dev`
3. Crea túnel Laravel: `cloudflared tunnel --url http://localhost:8000`
4. Crea túnel Vite: `cloudflared tunnel --url http://localhost:5173`

## Detener Servicios
Ejecuta `detener_tuneles.bat` para cerrar todos los servicios.

## Configuración para Móvil

### 1. Obtener las URLs
Cuando ejecutes los túneles, verás URLs como:
- Laravel: `https://reduces-portal-electronics-indiana.trycloudflare.com`
- Vite: `https://glance-autumn-apache-impose.trycloudflare.com`

### 2. Actualizar configuración
Edita `Clase1/.env` y actualiza:
```env
APP_URL_TUNNEL=https://tu-url-laravel.trycloudflare.com
APP_FRONTEND_URL_TUNNEL=https://tu-url-vite.trycloudflare.com
```

### 3. Actualizar Vite
Edita `Clase1/vite.config.js` y cambia la URL del servidor:
```js
server: {
    host: '0.0.0.0',
    hmr: {
        host: 'tu-url-vite.trycloudflare.com',
        protocol: 'wss'
    }
}
```

### 4. Acceder desde móvil
Abre en tu navegador móvil la URL del túnel de Vite.

## Notas Importantes
- Las URLs de trycloudflare.com cambian cada vez que reinicias el túnel
- Los túneles gratuitos no tienen garantía de uptime
- Para producción, usa túneles nombrados de Cloudflare
- Ya está configurado SANCTUM_STATEFUL_DOMAINS para aceptar *.trycloudflare.com
