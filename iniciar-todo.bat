@echo off
title ITFIP Maps - Iniciador Automatico
color 0A

echo.
echo ========================================================
echo          ITFIP MAPS - INICIADOR AUTOMATICO
echo     Backend Laravel + Frontend Vue
echo ========================================================
echo.

cd /d "%~dp0"

echo [1/6] Configurando archivos .env...
cd Clase1

if exist .env copy /y .env .env.backup >nul 2>&1

powershell -Command "(Get-Content .env) -replace '^SANCTUM_STATEFUL_DOMAINS=.*', 'SANCTUM_STATEFUL_DOMAINS=localhost,localhost:5173,127.0.0.1,127.0.0.1:5173' | Set-Content .env.temp"
move /y .env.temp .env >nul 2>&1

echo [2/6] Limpiando cache de Laravel...
php artisan config:clear >nul 2>&1
php artisan cache:clear >nul 2>&1

cd ..

echo [3/6] Configurando frontend...
cd itfip-map
if not exist .env (
    echo VITE_API_URL=http://localhost:8000/api > .env
)

cd ..

echo [4/6] Iniciando servidor Laravel (puerto 8000)...
start "Laravel Backend" cmd /k "cd /d "%~dp0Clase1" && php artisan serve"
timeout /t 3 /nobreak >nul

echo [5/6] Iniciando servidor Vite (puerto 5173)...
start "Vue Frontend" cmd /k "cd /d "%~dp0itfip-map" && npm run dev"
timeout /t 5 /nobreak >nul

echo.
echo ========================================================
echo                    TODO INICIADO
echo ========================================================
echo.
echo Se han abierto 2 ventanas:
echo   1. Laravel Backend (puerto 8000)
echo   2. Vue Frontend (puerto 5173)
echo.
echo IMPORTANTE: Si vas a Railway, no necesitas túneles Cloudflare.
echo.
pause
