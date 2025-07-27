# Laravel Live Server Starter
Write-Host "🚀 Starting Laravel Live Server..." -ForegroundColor Green
Write-Host ""
Write-Host "📡 Backend Server: http://localhost:8000" -ForegroundColor Cyan
Write-Host "🔥 Frontend Hot Reload: http://localhost:5173" -ForegroundColor Yellow
Write-Host ""
Write-Host "💡 Edit any file and see changes instantly!" -ForegroundColor Magenta
Write-Host ""

# Start Laravel Server in background
Write-Host "Starting Laravel Backend..." -ForegroundColor Blue
Start-Process -FilePath "php" -ArgumentList "artisan", "serve", "--host=0.0.0.0", "--port=8000" -WindowStyle Hidden

# Wait a moment
Start-Sleep -Seconds 2

# Start Vite Dev Server with Hot Reload
Write-Host "Starting Vite Hot Reload..." -ForegroundColor Blue
npm run dev