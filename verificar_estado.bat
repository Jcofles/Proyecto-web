@echo off
REM Script para verificar el estado de la aplicación en Windows

echo ================================
echo   VERIFICADOR DE ESTADO - ITFIP
echo ================================
echo.

REM Verificar Laravel
echo [1] Verificando Laravel en localhost:8000...
powershell -Command "$result = Test-NetConnection -ComputerName localhost -Port 8000 -InformationLevel Quiet; if($result) { Write-Host 'OK - Laravel esta corriendo' -ForegroundColor Green } else { Write-Host 'ERROR - Laravel no responde' -ForegroundColor Red }"
echo.

REM Verificar Vue
echo [2] Verificando Vue en localhost:5174...
powershell -Command "$result = Test-NetConnection -ComputerName localhost -Port 5174 -InformationLevel Quiet; if($result) { Write-Host 'OK - Vue esta corriendo' -ForegroundColor Green } else { Write-Host 'ERROR - Vue no responde' -ForegroundColor Red }"
echo.

REM Verificar archivo de logs
echo [3] Revisando archivo de logs de Laravel...
if exist "Clase1\storage\logs\laravel.log" (
    echo OK - laravel.log existe
    echo.
    echo Ultimas 10 lineas:
    powershell -Command "Get-Content 'Clase1\storage\logs\laravel.log' -Tail 10"
) else (
    echo ERROR - laravel.log no encontrado
)
echo.

REM Check .env files
echo [4] Verificando archivos .env...
if exist "Clase1\.env" (
    echo OK - Laravel .env existe
    powershell -Command "Select-String -Path 'Clase1\.env' -Pattern 'APP_FRONTEND_URL|MAIL_MAILER' | ForEach-Object { Write-Host $_.Line }"
) else (
    echo ERROR - Laravel .env no existe
)
echo.

if exist "itfip-map\.env" (
    echo OK - Vue .env existe
    powershell -Command "Select-String -Path 'itfip-map\.env' -Pattern 'VITE_API_URL' | ForEach-Object { Write-Host $_.Line }"
) else (
    echo ERROR - Vue .env no existe
)
echo.

echo ================================
echo   READY TO TEST?
echo ================================
echo.
echo Abre tu navegador en: http://localhost:5174/register
echo.
pause
