@echo off
echo ========================================
echo Iniciando Proyecto ITFIP Maps (Local)
echo ========================================
echo.

cd /d "%~dp0"

echo [1/2] Iniciando Backend Laravel...
start "Laravel API - http://localhost:8000" cmd /k "cd Clase1 && php artisan serve"
timeout /t 3 /nobreak >nul

echo [2/2] Iniciando Frontend Vue...
start "Frontend Vue - http://localhost:5173" cmd /k "cd itfip-map && npm run dev"

echo.
echo ========================================
echo Servidores Iniciados
echo ========================================
echo.
echo Backend API:  http://localhost:8000
echo Frontend:     http://localhost:5173
echo.
echo Abre tu navegador en: http://localhost:5173
echo.
pause
