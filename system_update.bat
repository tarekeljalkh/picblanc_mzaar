@echo off
setlocal
cd /d "%~dp0"

echo Fetching latest main...
git fetch origin main
if errorlevel 1 goto failed

echo Updating client files...
git checkout -f -B main origin/main
if errorlevel 1 goto failed

echo Client updated successfully.
pause
exit /b 0

:failed
echo Update failed. Check the Git/network setup.
pause
exit /b 1