@echo off
title Actualizar URLs de Tuneles
color 0B

echo.
echo ========================================================
echo        ACTUALIZAR URLs DE TUNELES CLOUDFLARE
echo ========================================================
echo.

cd /d "%~dp0"

set /p BACKEND_URL="Ingresa la URL del tunel BACKEND (ej: https://xxx.trycloudflare.com): "
set /p FRONTEND_URL="Ingresa la URL del tunel FRONTEND (ej: https://yyy.trycloudflare.com): "

echo.
echo [1/4] Actualizando .env del backend...
cd Clase1

powershell -Command "(Get-Content .env) -replace '^APP_URL=.*', 'APP_URL=%BACKEND_URL%' | Set-Content .env.temp"
move /y .env.temp .env >nul 2>&1

powershell -Command "(Get-Content .env) -replace '^APP_FRONTEND_URL=.*', 'APP_FRONTEND_URL=%FRONTEND_URL%' | Set-Content .env.temp"
move /y .env.temp .env >nul 2>&1

findstr /C:"APP_FRONTEND_URL" .env >nul 2>&1
if errorlevel 1 (
    echo APP_FRONTEND_URL=%FRONTEND_URL% >> .env
)

echo [2/4] Limpiando cache de Laravel...
php artisan config:clear >nul 2>&1
php artisan cache:clear >nul 2>&1

cd ..

echo [3/4] Actualizando .env del frontend...
cd itfip-map

powershell -Command "(Get-Content .env) -replace '^VITE_API_URL=.*', 'VITE_API_URL=%BACKEND_URL%/api' | Set-Content .env.temp"
move /y .env.temp .env >nul 2>&1

findstr /C:"VITE_API_URL" .env >nul 2>&1
if errorlevel 1 (
    echo VITE_API_URL=%BACKEND_URL%/api > .env
)

cd ..

echo [4/4] Configuracion completada
echo.
echo ========================================================
echo                    CONFIGURACION LISTA
echo ========================================================
echo.
echo URLs configuradas:
echo   Backend:  %BACKEND_URL%
echo   Frontend: %FRONTEND_URL%
echo   API:      %BACKEND_URL%/api
echo.
echo IMPORTANTE: Reinicia los servidores Laravel y Vite
echo para que los cambios surtan efecto.
echo.
pause
