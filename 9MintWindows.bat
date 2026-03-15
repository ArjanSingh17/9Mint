@echo off
setlocal
cd /d "%~dp0"

REM
if not exist "node_modules" (
  echo node_modules not found. Installing npm dependencies...
  npm install
)

REM
start "Laravel server" cmd /k "php artisan serve --host=127.0.0.1 --port=8000"
start "Frontend dev" cmd /k "npm run dev -- --host 127.0.0.1 --port 5173"

timeout /t 5 /nobreak >nul
start "" "http://127.0.0.1:8000/homepage"
endlocal