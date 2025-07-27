@echo off
echo 🚀 Starting Laravel Live Server...
echo.
echo 📡 Backend Server: http://localhost:8000
echo 🔥 Frontend Hot Reload: http://localhost:5173
echo.
echo 💡 Edit any file and see changes instantly!
echo.

REM Start Laravel Server in background
start /B php artisan serve --host=0.0.0.0 --port=8000

REM Wait a moment
timeout /t 2 /nobreak >nul

REM Start Vite Dev Server with Hot Reload
npm run dev

pause