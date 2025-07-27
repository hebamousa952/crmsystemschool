# رفع المشروع إلى GitHub
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "    رفع المشروع إلى GitHub" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

Set-Location "c:\xampp\htdocs\crmsystemschool"

Write-Host ""
Write-Host "🚀 جاري رفع الملفات إلى GitHub..." -ForegroundColor Yellow
Write-Host ""

# إعداد Git credentials مؤقتاً
$env:GIT_ASKPASS = "echo"
$env:GIT_USERNAME = "hebamousa952"
$env:GIT_PASSWORD = "Hh@01111585527"

# محاولة الرفع
try {
    git push -u origin master
    Write-Host ""
    Write-Host "✅ تم رفع المشروع بنجاح!" -ForegroundColor Green
    Write-Host "🌐 يمكنك مراجعة المشروع على:" -ForegroundColor Cyan
    Write-Host "https://github.com/hebamousa952/crmsystemschool" -ForegroundColor Blue
} catch {
    Write-Host "❌ حدث خطأ في الرفع" -ForegroundColor Red
    Write-Host "جرب الرفع اليدوي:" -ForegroundColor Yellow
    Write-Host "Username: hebamousa952" -ForegroundColor White
    Write-Host "Password: Hh@01111585527" -ForegroundColor White
}

Write-Host ""
Read-Host "اضغط Enter للمتابعة..."