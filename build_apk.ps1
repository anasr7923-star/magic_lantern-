Write-Host "🧹 جاري تنظيف وتطهير كاش فلاتر قسرياً..." -ForegroundColor Cyan
flutter clean

Write-Host "🏗️ جاري مسح مجلد أندرويد المعلق وإعادة توليده على مية بيضا..." -ForegroundColor Yellow
Remove-Item -Path "android" -Recurse -Force -ErrorAction SilentlyContinue
flutter create --platforms=android .

Write-Host "📥 جاري شحن وتحديث حزم السيرفر والـ GPS..." -ForegroundColor Blue
flutter pub get

Write-Host "🚀 انطلاق محرك التجميع النهائي للـ APK بدون أخطاء..." -ForegroundColor Green
flutter build apk --target-platform android-arm64

Write-Host "🏆 مبروك يا هندسة! الملف جاهز الحين للرفع والتثبيت!" -ForegroundColor Green
Read-Host "اضغط Enter لغلق النافذة..."