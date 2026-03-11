@echo off
setlocal

echo ============================================
echo   NFT Thumbnail Backfill Tool
echo   Rebuilds missing/deleted thumbnails
echo ============================================
echo.

cd /d "%~dp0.."

echo Collections:
echo   0. All collections
php artisan tinker --execute="App\Models\Collection::query()->orderBy('id')->get(['id','name'])->each(function($c){echo '  '.$c->id.'. '.$c->name.PHP_EOL;});"
echo.
set /p COLLECTION_CHOICE=Type collection number (0 = all): 
if "%COLLECTION_CHOICE%"=="" set COLLECTION_CHOICE=0

echo.
echo Run mode:
echo   1. Missing only (also repairs deleted thumbnail files)
echo   2. Force re-generate all in selected scope
set /p MODE_CHOICE=Choose mode [1/2] (default 1): 
if "%MODE_CHOICE%"=="" set MODE_CHOICE=1

set "CMD=php artisan nfts:backfill-thumbnails"
if not "%COLLECTION_CHOICE%"=="0" set "CMD=%CMD% --collection-id=%COLLECTION_CHOICE%"
if "%MODE_CHOICE%"=="2" set "CMD=%CMD% --force"

echo.
echo Running: %CMD%
%CMD%

echo.
echo Done. Press any key to close.
pause >nul
endlocal
