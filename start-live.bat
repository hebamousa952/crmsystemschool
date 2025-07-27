@echo off
cls
echo.
echo ========================================
echo 🔥 Laravel Live Server Starting...
echo ========================================
echo.
echo 📡 Server URL: http://localhost:8000
echo 🚀 Admin Panel: http://localhost:8000/admin
echo 🔥 Live Reload: ACTIVE
echo.
echo 💡 Edit any file and see changes instantly!
echo ========================================
echo.

php artisan serve --host=0.0.0.0 --port=8000