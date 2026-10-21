@echo off
setlocal
cd /d "%~dp0"

echo Fetching latest master...
git fetch origin master
if errorlevel 1 goto failed

echo Updating client files...
git checkout -f -B master origin/master
if errorlevel 1 goto failed

echo Clearing Laravel cache...
php artisan optimize:clear

echo Client updated successfully.
pause
exit /b 0

:failed
echo Update failed. Check the Git/network setup.
pause
exit /b 1
