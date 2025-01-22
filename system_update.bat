@echo off
:: Navigate to the directory where the .bat file is located
cd /d "%~dp0"

:: Fetch the latest changes from the remote repository
echo Checking for updates...
git fetch origin master

:: Compare the local and remote branches
for /f "tokens=*" %%i in ('git rev-parse HEAD') do set LOCAL_HASH=%%i
for /f "tokens=*" %%i in ('git rev-parse origin/master') do set REMOTE_HASH=%%i

if "%LOCAL_HASH%"=="%REMOTE_HASH%" (
    echo No updates found. Your repository is up-to-date.
) else (
    echo Updates found. Pulling changes...
    git pull origin master
    if %errorlevel%==0 (
        echo System updated successfully.
    ) else (
        echo An error occurred while pulling changes. Please check your setup.
    )
)

pause
