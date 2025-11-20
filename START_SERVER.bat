@echo off
echo Starting PHP Development Server...
echo.
echo Server will be available at: http://localhost:8000
echo.
echo Press Ctrl+C to stop the server
echo.

REM Check if PHP is available
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP is not installed or not in your PATH
    echo.
    echo Please install PHP from: https://windows.php.net/download/
    echo Or add PHP to your system PATH
    echo.
    pause
    exit /b 1
)

REM Start PHP server with router
php -S localhost:8000 -t public public/router.php

pause

