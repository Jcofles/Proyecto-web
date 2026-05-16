# Railway Deployment Guide for Proyecto-web

## Qué debes hacer en Railway

1. En Railway, crea un nuevo proyecto y conecta este repositorio.
2. En la configuración del servicio, establece la carpeta raíz en `Clase1`.
   - Si no puedes cambiar la carpeta raíz, usa el `Procfile` que se añadió a la raíz del repo.
3. Configura estas variables de entorno en Railway:
   - `APP_KEY` = base64:... (genera localmente con `php artisan key:generate --show`)
   - `APP_URL` = https://<tu-backend>.up.railway.app
   - `APP_FRONTEND_URL` = https://<tu-frontend>.up.railway.app
   - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
   - `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`
   - `VITE_API_URL` = https://<tu-backend>.up.railway.app/api
   - `SANCTUM_STATEFUL_DOMAINS` = <tu-frontend>.up.railway.app
   - `SESSION_DOMAIN` = <tu-frontend>.up.railway.app

## Comandos recomendados

- Build command (si Railway pide uno):
  - `cd Clase1 && composer install --no-dev --optimize-autoloader`
- Start command (si Railway pide uno):
  - `cd Clase1 && php artisan serve --host=0.0.0.0 --port=${PORT}`

## Notas importantes

- Si Railway detecta el repo en la raíz, el `Procfile` de la raíz ayudará a iniciar el backend.
- Si el servicio sigue crasheando, suele ser por:
  - `APP_KEY` no configurada.
  - credenciales de DB incorrectas.
  - `release` o migraciones automáticas que fallan antes de que la base de datos esté lista.

## Qué hacer si sigue fallando

1. Revisa el `Deploy Logs` en Railway.
2. Si el error ocurre en la fase de `release`, desactiva temporalmente `release` y ejecuta `php artisan migrate --force` manualmente cuando la base de datos esté lista.
3. Si el error ocurre al iniciar el servidor, revisa que el servicio use la carpeta `Clase1` o el `Procfile` raíz.
