@echo off
echo ========================================
echo Deteniendo Tuneles y Servidores
echo ========================================
echo.

taskkill /FI "WINDOWTITLE eq Laravel Server*" /T /F >nul 2>&1
taskkill /FI "WINDOWTITLE eq Vite Dev Server*" /T /F >nul 2>&1
taskkill /FI "WINDOWTITLE eq Cloudflare Tunnel*" /T /F >nul 2>&1
taskkill /IM cloudflared.exe /F >nul 2>&1
taskkill /IM php.exe /F >nul 2>&1
taskkill /IM node.exe /F >nul 2>&1

echo Todos los servicios han sido detenidos
echo.
pause
