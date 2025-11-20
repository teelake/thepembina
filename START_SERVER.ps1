# PowerShell script to start PHP development server

Write-Host "Starting PHP Development Server..." -ForegroundColor Green
Write-Host ""
Write-Host "Server will be available at: http://localhost:8000" -ForegroundColor Cyan
Write-Host ""
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host ""

# Check if PHP is available
try {
    $phpVersion = php --version 2>&1
    if ($LASTEXITCODE -ne 0) {
        throw "PHP not found"
    }
    Write-Host "PHP found!" -ForegroundColor Green
    Write-Host ""
} catch {
    Write-Host "ERROR: PHP is not installed or not in your PATH" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please install PHP from: https://windows.php.net/download/" -ForegroundColor Yellow
    Write-Host "Or add PHP to your system PATH" -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

# Start PHP server
Write-Host "Starting server..." -ForegroundColor Green
php -S localhost:8000 -t public public/router.php

