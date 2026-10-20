@echo off
title Sistema de Gestion de Pagos - Urbanizacion Altamira
REM ====================================================
REM  Inicia el Sistema de Gestion de Pagos.
REM  1. Enciende Apache y MySQL de XAMPP (si estan apagados)
REM  2. Abre el sistema en su propia ventana, sin barra
REM     de direcciones, como un programa de escritorio.
REM  No necesita internet.
REM ====================================================

tasklist | find /i "httpd.exe" >nul || start "" /min "C:\xampp\apache_start.bat"
tasklist | find /i "mysqld.exe" >nul || start "" /min "C:\xampp\mysql_start.bat"

echo Iniciando el sistema, espere un momento...
timeout /t 4 /nobreak >nul

set EDGE=%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe
if not exist "%EDGE%" set EDGE=%ProgramFiles%\Microsoft\Edge\Application\msedge.exe

if exist "%EDGE%" (
    start "" "%EDGE%" --app=http://localhost/altamira/ --window-size=1366,800
) else (
    start "" http://localhost/altamira/
)
exit
