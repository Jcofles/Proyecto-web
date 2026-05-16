# Despliegue en Railway (guía rápida)

Pasos mínimos para desplegar el backend Laravel y el frontend en Railway:

1. Repositorio
   - Empuja el repositorio a GitHub (o conecta GitHub con Railway).

2. Aplicación en Railway
   - Crea un nuevo proyecto en https://railway.com/dashboard y conecta el repositorio.
   - En "Environment Variables" agrega las variables del `.env` necesarias (APP_KEY, DB_*, MAIL_*, APP_URL, APP_FRONTEND_URL, etc.).

3. Build / Start
   - Railway detecta PHP/Laravel. Asegúrate de tener `composer.json` en la raíz de `Clase1` o configura el root path en Railway al folder `Clase1`.
   - Comandos comunes:
     - Build: `composer install --no-dev --optimize-autoloader` (o déjalo por defecto)
     - Start: deja la detección de Railway o usa `php artisan serve --host=0.0.0.0 --port=$PORT`

Nota: He añadido un `Procfile` en el folder `Clase1/` con un `web` command y un `release` command para migraciones automáticas.
Si Railway no usa la carpeta `Clase1` como root, también creé un `Procfile` en la raíz del proyecto que ejecuta `cd Clase1 && php artisan serve ...`.
   - Puedes desplegar el frontend como un servicio separado (Node) en Railway o servirlo como contenido estático.
   - Establece `VITE_API_URL` a la URL pública del backend (`https://<tu-backend>.up.railway.app/api`).

6. SANCTUM / CORS
   - En `Clase1/.env`, configura `SANCTUM_STATEFUL_DOMAINS` con los dominios del frontend (por ejemplo `mi-frontend.up.railway.app`).
   - Asegúrate de que `SESSION_DOMAIN` y `APP_FRONTEND_URL` apunten al dominio correcto.

Generar `APP_KEY` y setear en Railway:

1. Genera la key localmente y cópiala:

```bash
cd Clase1
php artisan key:generate --show
```

2. En Railway, en Environment Variables, pega el valor en `APP_KEY`.

Ejemplo de variables mínimas que debes añadir en Railway (usa los valores reales que Railway provea):

- `APP_KEY` = base64:...
- `APP_URL` = https://<tu-backend>.up.railway.app
- `APP_FRONTEND_URL` = https://<tu-frontend>.up.railway.app
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`

Release command (en Railway):
- Usa: `php artisan migrate --force` para ejecutar migraciones automáticas después del deploy. También incluí `release: php artisan migrate --force` en `Clase1/Procfile`.

Front-end en Railway:
- Sube el frontend como servicio separado o como static site. Asegúrate de definir `VITE_API_URL` apuntando al backend público.

Archivos añadidos al repositorio para ayudarte:

- `Clase1/Procfile` — comando `web` y `release`.
- `Clase1/.env.production.example` — plantilla con variables necesarias para producción.

Si quieres, puedo:

- Crear un pequeño script de `deploy.sh` que genere `APP_KEY` y lo suba a GitHub Actions (necesitaría detalles de CI). 
- Añadir un `README` más detallado con screenshots para los pasos en Railway.


7. Tareas finales
   - `php artisan config:clear` y `php artisan cache:clear` si haces cambios en `.env`.
   - Revisa `storage/logs/laravel.log` si hay errores.

Si quieres, puedo crear un `Procfile` o ajustar el `composer.json`/scripts para mejorar la integración con Railway.
