@echo off
cd /d "%~dp0"
start "Laravel server" cmd /k "php artisan serve"
start "Frontend dev" cmd /k "npm run dev"
timeout /t 10 /nobreak >nul
start "" "http://127.0.0.1:8000"