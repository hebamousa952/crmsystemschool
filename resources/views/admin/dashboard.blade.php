@extends('layouts.admin')

@section('title', 'لوحة التحكم - نظام إدارة المدرسة')
@section('page-title', 'لوحة التحكم')

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold mb-2">🚀 مرحباً، {{ auth()->user()->name ?? 'مدير النظام' }} - Responsive System Active!</h1>
                <p class="text-blue-100">إليك ملخص سريع عن حالة المدرسة اليوم</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <button onclick="responsiveSidebar.toggle()" class="bg-white/20 hover:bg-white/30 px-3 py-1 rounded text-sm transition-colors">
                        🔄 تبديل السايد بار
                    </button>
                    <span id="breakpoint-indicator" class="bg-white/20 px-3 py-1 rounded text-sm">
                        📱 جاري التحميل...
                    </span>
                </div>
            </div>
            <div class="hidden md:block">
                <i class="fas fa-school text-6xl text-blue-200"></i>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Students -->
        <div class="bg-primary rounded-xl p-6 shadow-sm border border-custom">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary text-sm font-medium">إجمالي الطلاب</p>
                    <p class="text-3xl font-bold text-primary mt-2">1,247</p>
                    <p class="text-green-600 text-sm mt-1">
                        <i class="fas fa-arrow-up"></i> +12 هذا الشهر
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-graduate text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Teachers -->
        <div class="bg-primary rounded-xl p-6 shadow-sm border border-custom">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary text-sm font-medium">إجمالي المعلمين</p>
                    <p class="text-3xl font-bold text-primary mt-2">89</p>
                    <p class="text-green-600 text-sm mt-1">
                        <i class="fas fa-arrow-up"></i> +3 هذا الشهر
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chalkboard-teacher text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="bg-primary rounded-xl p-6 shadow-sm border border-custom">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary text-sm font-medium">الإيرادات الشهرية</p>
                    <p class="text-3xl font-bold text-primary mt-2">₪45,280</p>
                    <p class="text-green-600 text-sm mt-1">
                        <i class="fas fa-arrow-up"></i> +8.2% من الشهر الماضي
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Pending Payments -->
        <div class="bg-primary rounded-xl p-6 shadow-sm border border-custom">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-secondary text-sm font-medium">المدفوعات المعلقة</p>
                    <p class="text-3xl font-bold text-primary mt-2">₪12,450</p>
                    <p class="text-red-600 text-sm mt-1">
                        <i class="fas fa-exclamation-triangle"></i> يتطلب متابعة
                    </p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts and Recent Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Chart -->
        <div class="bg-primary rounded-xl p-6 shadow-sm border border-custom">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-primary">الإيرادات الشهرية</h3>
                <select class="text-sm border border-custom rounded-lg px-3 py-1 bg-primary">
                    <option>آخر 6 أشهر</option>
                    <option>آخر سنة</option>
                </select>
            </div>
            <div class="h-64 flex items-center justify-center bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="text-center">
                    <i class="fas fa-chart-line text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">سيتم إضافة الرسم البياني هنا</p>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-primary rounded-xl p-6 shadow-sm border border-custom">
            <h3 class="text-lg font-semibold text-primary mb-4">الأنشطة الأخيرة</h3>
            <div class="space-y-4">
                <div class="flex items-start space-x-3 space-x-reverse">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user-plus text-blue-600 text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-primary">تم تسجيل طالب جديد</p>
                        <p class="text-xs text-secondary">أحمد محمد علي - الصف الثالث الابتدائي</p>
                        <p class="text-xs text-secondary">منذ 5 دقائق</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3 space-x-reverse">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-money-bill text-green-600 text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-primary">تم استلام دفعة مالية</p>
                        <p class="text-xs text-secondary">₪500 من ولي أمر سارة أحمد</p>
                        <p class="text-xs text-secondary">منذ 15 دقيقة</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3 space-x-reverse">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-bell text-yellow-600 text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-primary">تذكير بموعد اجتماع</p>
                        <p class="text-xs text-secondary">اجتماع أولياء الأمور غداً الساعة 3:00 م</p>
                        <p class="text-xs text-secondary">منذ ساعة</p>
                    </div>
                </div>

                <div class="flex items-start space-x-3 space-x-reverse">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-graduation-cap text-purple-600 text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-primary">تم رفع درجات الامتحان</p>
                        <p class="text-xs text-secondary">امتحان الرياضيات للصف الخامس</p>
                        <p class="text-xs text-secondary">منذ ساعتين</p>
                    </div>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-custom">
                <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    عرض جميع الأنشطة <i class="fas fa-arrow-left mr-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-primary rounded-xl p-6 shadow-sm border border-custom">
        <h3 class="text-lg font-semibold text-primary mb-4">إجراءات سريعة</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <a href="#" class="flex flex-col items-center p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-user-plus text-blue-600"></i>
                </div>
                <span class="text-sm font-medium text-primary">إضافة طالب</span>
            </a>

            <a href="#" class="flex flex-col items-center p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-money-bill text-green-600"></i>
                </div>
                <span class="text-sm font-medium text-primary">تسجيل دفعة</span>
            </a>

            <a href="#" class="flex flex-col items-center p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-file-alt text-yellow-600"></i>
                </div>
                <span class="text-sm font-medium text-primary">إنشاء تقرير</span>
            </a>

            <a href="#" class="flex flex-col items-center p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-bell text-purple-600"></i>
                </div>
                <span class="text-sm font-medium text-primary">إرسال إشعار</span>
            </a>

            <a href="#" class="flex flex-col items-center p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-calendar text-red-600"></i>
                </div>
                <span class="text-sm font-medium text-primary">جدولة حدث</span>
            </a>

            <a href="#" class="flex flex-col items-center p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mb-2">
                    <i class="fas fa-cog text-indigo-600"></i>
                </div>
                <span class="text-sm font-medium text-primary">الإعدادات</span>
            </a>
        </div>
    </div>

    <!-- Upcoming Events -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Calendar -->
        <div class="bg-primary rounded-xl p-6 shadow-sm border border-custom">
            <h3 class="text-lg font-semibold text-primary mb-4">الأحداث القادمة</h3>
            <div class="space-y-3">
                <div class="flex items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center text-white text-sm font-bold">
                        15
                    </div>
                    <div class="mr-3 flex-1">
                        <p class="font-medium text-primary">اجتماع أولياء الأمور</p>
                        <p class="text-sm text-secondary">الساعة 3:00 م - قاعة الاجتماعات</p>
                    </div>
                </div>

                <div class="flex items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                    <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center text-white text-sm font-bold">
                        18
                    </div>
                    <div class="mr-3 flex-1">
                        <p class="font-medium text-primary">امتحان الفصل الأول</p>
                        <p class="text-sm text-secondary">جميع المراحل - بداية من الساعة 8:00 ص</p>
                    </div>
                </div>

                <div class="flex items-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                    <div class="w-10 h-10 bg-yellow-600 rounded-lg flex items-center justify-center text-white text-sm font-bold">
                        22
                    </div>
                    <div class="mr-3 flex-1">
                        <p class="font-medium text-primary">يوم مفتوح للمدرسة</p>
                        <p class="text-sm text-secondary">من الساعة 9:00 ص حتى 2:00 م</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="bg-primary rounded-xl p-6 shadow-sm border border-custom">
            <h3 class="text-lg font-semibold text-primary mb-4">الإشعارات المهمة</h3>
            <div class="space-y-3">
                <div class="flex items-start p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border-r-4 border-red-500">
                    <i class="fas fa-exclamation-triangle text-red-500 mt-1"></i>
                    <div class="mr-3">
                        <p class="font-medium text-primary">متأخرات مالية</p>
                        <p class="text-sm text-secondary">15 طالب لديهم متأخرات تزيد عن شهر</p>
                    </div>
                </div>

                <div class="flex items-start p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border-r-4 border-yellow-500">
                    <i class="fas fa-clock text-yellow-500 mt-1"></i>
                    <div class="mr-3">
                        <p class="font-medium text-primary">انتهاء صلاحية</p>
                        <p class="text-sm text-secondary">3 معلمين تنتهي عقودهم هذا الشهر</p>
                    </div>
                </div>

                <div class="flex items-start p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border-r-4 border-blue-500">
                    <i class="fas fa-info-circle text-blue-500 mt-1"></i>
                    <div class="mr-3">
                        <p class="font-medium text-primary">تحديث النظام</p>
                        <p class="text-sm text-secondary">يتوفر تحديث جديد للنظام</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Update breakpoint indicator
function updateBreakpointIndicator() {
    const indicator = document.getElementById('breakpoint-indicator');
    if (indicator && responsiveSidebar) {
        const breakpoint = responsiveSidebar.getBreakpoint();
        const isExpanded = responsiveSidebar.sidebarExpanded;
        
        const icons = {
            mobile: '📱',
            tablet: '📟', 
            desktop: '🖥️'
        };
        
        indicator.textContent = `${icons[breakpoint]} ${breakpoint} ${isExpanded ? '(مفتوح)' : '(مغلق)'}`;
    }
}

// Update indicator when page loads and on resize
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        updateBreakpointIndicator();
        window.addEventListener('resize', updateBreakpointIndicator);
    }, 300);
});
</script>
@endsection