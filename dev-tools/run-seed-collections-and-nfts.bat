@echo off
echo ============================================
echo   NFT Collection Seeder
echo   Seeds collections and NFTs into the
echo   database for local development.
echo ============================================
echo.

php "%~dp0seed-collections-and-nfts.php"
set "exitCode=%ERRORLEVEL%"

echo.
if "%exitCode%"=="0" (
    echo Seed completed successfully. Press any key to close.
) else (
    echo Seed failed with exit code %exitCode%. Review the error above.
)

pause >nul
exit /b %exitCode%