@echo off
setlocal
cd /d "%~dp0"

where git >nul 2>&1
if errorlevel 1 goto missing_git

where php >nul 2>&1
if errorlevel 1 goto missing_php

echo Fetching latest master...
git fetch origin master
if errorlevel 1 goto failed

echo Updating client files...
git checkout -f -B master origin/master
if errorlevel 1 goto failed

for /f "delims=" %%C in ('git rev-parse --short HEAD') do set "installed_commit=%%C"
echo Installed commit: %installed_commit%

echo Clearing Laravel cache...
php artisan optimize:clear
if errorlevel 1 goto failed

echo Client updated successfully to %installed_commit%.
pause
exit /b 0

:missing_git
echo Update failed: Git is not installed or is not available in PATH.
pause
exit /b 1

:missing_php
echo Update failed: PHP is not installed or is not available in PATH.
pause
exit /b 1

:failed
echo Update failed. Check the Git/network setup.
pause
exit /b 1
