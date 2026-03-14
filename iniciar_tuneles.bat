@echo off
echo ========================================
echo Iniciando Tuneles de Cloudflare
echo ========================================
echo.

cd /d "%~dp0"

echo [1/4] Iniciando Laravel (puerto 8000)...
start "Laravel Server" cmd /k "cd Clase1 && php artisan serve"
timeout /t 5 /nobreak >nul

echo [2/4] Iniciando Vite (puerto 5173)...
start "Vite Dev Server" cmd /k "cd itfip-map && npm run dev"
echo Esperando a que Vite este listo...
timeout /t 10 /nobreak >nul

echo [3/4] Creando tunel para Laravel...
start "Cloudflare Tunnel - Laravel" cmd /k "cloudflared tunnel --url http://localhost:8000"
timeout /t 5 /nobreak >nul

echo [4/4] Creando tunel para Vite...
start "Cloudflare Tunnel - Vite" cmd /k "cloudflared tunnel --url http://localhost:5173"

echo.
echo ========================================
echo Tuneles iniciados correctamente
echo ========================================
echo.
echo IMPORTANTE: Espera 10-15 segundos antes de usar las URLs
echo Revisa las ventanas abiertas para ver las URLs publicas
echo Presiona cualquier tecla para cerrar esta ventana...
pause >nul
