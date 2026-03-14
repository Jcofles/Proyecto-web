# Verificar Túneles de Cloudflare

## Problema Actual
Estás usando la URL incorrecta en el archivo `.env` del frontend.

## Cómo Identificar Cada Túnel

### 1. Túnel de Laravel (Backend - Puerto 8000)
- Busca la ventana: **"Cloudflare Tunnel - Laravel"**
- Copia la URL que aparece (ejemplo: `https://xyz-abc-123.trycloudflare.com`)
- Esta URL debe ir en: `itfip-map/.env` como `VITE_API_URL`

### 2. Túnel de Vite (Frontend - Puerto 5173)
- Busca la ventana: **"Cloudflare Tunnel - Vite"**
- Copia la URL que aparece (ejemplo: `https://abc-xyz-456.trycloudflare.com`)
- Esta URL es la que abres en tu navegador/móvil

## Pasos para Corregir

1. **Identifica la URL del túnel de Laravel** (ventana "Cloudflare Tunnel - Laravel")
   
2. **Actualiza `itfip-map/.env`**:
   ```env
   VITE_API_URL=https://TU-URL-LARAVEL.trycloudflare.com/api
   ```
   ⚠️ **IMPORTANTE**: Debe terminar en `/api`

3. **Reinicia Vite**:
   - Ve a la ventana "Vite Dev Server"
   - Presiona Ctrl+C para detener
   - Ejecuta: `npm run dev`

4. **Prueba el login**:
   - Abre la URL del túnel de **Vite** en tu navegador
   - Intenta iniciar sesión

## Verificar que Laravel Responde

Abre en tu navegador:
```
https://TU-URL-LARAVEL.trycloudflare.com/api/nodos
```

Deberías ver una respuesta JSON con los nodos o un array vacío `[]`.

Si ves un error 404, significa que:
- La URL es incorrecta
- Laravel no está corriendo
- El túnel no está apuntando al puerto correcto

## Solución Alternativa (Desarrollo Local)

Si los túneles te causan problemas, puedes trabajar localmente:

1. **Detén los túneles** (ejecuta `detener_tuneles.bat`)

2. **Actualiza `itfip-map/.env`**:
   ```env
   VITE_API_URL=http://localhost:8000/api
   ```

3. **Reinicia Vite**

4. **Accede desde tu PC**:
   ```
   http://localhost:5173
   ```

Esto funcionará perfectamente en tu computadora, pero no desde tu móvil.
