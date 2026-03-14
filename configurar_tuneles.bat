@echo off
echo ========================================
echo Configuracion de Tuneles Cloudflare
echo ========================================
echo.

cd /d "%~dp0"

echo [1/2] Iniciando tuneles...
echo.

REM Iniciar Laravel
start "Laravel Server" cmd /k "cd Clase1 && php artisan serve"
timeout /t 5 /nobreak >nul

REM Iniciar Vite
start "Vite Dev Server" cmd /k "cd itfip-map && npm run dev"
timeout /t 10 /nobreak >nul

REM Iniciar túnel Laravel y capturar URL
echo [2/2] Creando tuneles...
start "Cloudflare Tunnel - Laravel" cmd /k "cloudflared tunnel --url http://localhost:8000"
timeout /t 5 /nobreak >nul

REM Iniciar túnel Vite
start "Cloudflare Tunnel - Vite" cmd /k "cloudflared tunnel --url http://localhost:5173"

echo.
echo ========================================
echo IMPORTANTE: Configuracion Manual
echo ========================================
echo.
echo 1. Busca las URLs en las ventanas de Cloudflare
echo    - Una URL para Laravel (puerto 8000)
echo    - Una URL para Vite (puerto 5173)
echo.
echo 2. Actualiza el archivo: itfip-map\.env
echo    VITE_API_URL=https://tu-url-laravel.trycloudflare.com/api
echo.
echo 3. Reinicia el servidor de Vite (Ctrl+C y npm run dev)
echo.
echo 4. Accede a la URL de Vite en tu navegador
echo.
pause
