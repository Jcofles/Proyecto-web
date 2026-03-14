@echo off
echo ========================================
echo Diagnostico de Conexion API
echo ========================================
echo.

echo Probando conexion al backend...
curl -X GET http://localhost:8000/api/nodos -H "Accept: application/json" -H "Content-Type: application/json"

echo.
echo.
echo Si ves JSON arriba, el backend funciona correctamente.
echo Si ves HTML o error, revisa que Laravel este corriendo.
echo.
pause
