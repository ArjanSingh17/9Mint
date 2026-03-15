@echo off
setlocal

php "%~dp0seed-collections-and-nfts.php"
set "exitCode=%ERRORLEVEL%"

echo.
if "%exitCode%"=="0" (
    echo Seed completed successfully. You may close this window.
) else (
    echo Seed failed with exit code %exitCode%. Review the error above.
)

pause
exit /b %exitCode%