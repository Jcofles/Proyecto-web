@echo off
REM Script para probar el envío de correos con Gmail SMTP vía Tinker
REM Ejecuta esto desde la carpeta Clase1/

echo ========================================
echo  Iniciando prueba de Gmail SMTP
echo ========================================
echo.
echo IMPORTANTE: Cuando se abra la consola de Tinker, pega este código:
echo.
echo ---
echo Mail::raw('Correo de prueba desde Laravel local con Gmail SMTP', function($message) {
echo     $message->to('tu-email@gmail.com')->subject('Prueba Gmail SMTP');
echo });
echo ---
echo.
echo Luego escribe: exit
echo.
pause

php artisan tinker
