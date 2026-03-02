@echo off
REM Limpiar cachés de Laravel después de cambios en .env
REM Este script debe ejecutarse desde una terminal en la carpeta Clase1/

echo ========================================
echo  Limpiando Cachés de Laravel...
echo ========================================
echo.

php artisan config:clear
if %errorlevel% neq 0 (
    echo ERROR: No se pudo ejecutar config:clear
    pause
    exit /b 1
)

php artisan cache:clear
if %errorlevel% neq 0 (
    echo ERROR: No se pudo ejecutar cache:clear
    pause
    exit /b 1
)

php artisan view:clear
if %errorlevel% neq 0 (
    echo ERROR: No se pudo ejecutar view:clear
    pause
    exit /b 1
)

echo.
echo ========================================
echo  ✓ Cachés limpiados correctamente
echo ========================================
echo.
echo Ahora puedes probar el registro de usuarios.
echo.
pause
