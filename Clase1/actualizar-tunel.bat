@echo off
echo ========================================
echo   CONFIGURADOR DE TUNELES CLOUDFLARE
echo   ITFIP Maps - Actualizacion de URLs
echo ========================================
echo.

REM Solicitar URLs del tunel
set /p BACKEND_URL="Ingresa la URL del tunel del BACKEND (ej: https://dock-lucas-propose-pro.trycloudflare.com): "
set /p FRONTEND_URL="Ingresa la URL del tunel del FRONTEND (ej: https://glance-autumn-apache-impose.trycloudflare.com): "

echo.
echo Actualizando archivo .env...
echo.

REM Crear backup del .env
copy .env .env.backup >nul 2>&1
echo [OK] Backup creado: .env.backup

REM Actualizar APP_URL
powershell -Command "(Get-Content .env) -replace '^APP_URL=.*', 'APP_URL=%BACKEND_URL%' | Set-Content .env.temp"
move /y .env.temp .env >nul 2>&1
echo [OK] APP_URL actualizado

REM Actualizar APP_FRONTEND_URL
powershell -Command "(Get-Content .env) -replace '^APP_FRONTEND_URL=.*', 'APP_FRONTEND_URL=%FRONTEND_URL%' | Set-Content .env.temp"
move /y .env.temp .env >nul 2>&1
echo [OK] APP_FRONTEND_URL actualizado

echo.
echo Limpiando cache de Laravel...
php artisan config:clear
php artisan cache:clear

echo.
echo ========================================
echo   CONFIGURACION COMPLETADA
echo ========================================
echo.
echo URLs configuradas:
echo   Backend:  %BACKEND_URL%
echo   Frontend: %FRONTEND_URL%
echo.
echo Ahora actualiza el archivo .env del frontend:
echo   cd ..\itfip-map
echo   Edita .env y cambia VITE_API_URL=%BACKEND_URL%/api
echo.
echo Luego reinicia ambos servidores:
echo   Backend:  php artisan serve
echo   Frontend: npm run dev
echo.
pause
