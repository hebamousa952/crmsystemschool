<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'نظام إدارة المدرسة')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts - Cairo -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Responsive CSS -->
    <link href="{{ asset('css/responsive-admin.css') }}" rel="stylesheet">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Cairo', sans-serif; }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
        
        /* 🚀 Professional Fixed Sidebar System */
        .sidebar-transition { 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        .content-transition { 
            transition: margin-right 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        
        /* 🖥️ Desktop (1024px+) - Fixed Sidebar Layout */
        @media (min-width: 1024px) {
            #app {
                display: flex;
                flex-direction: row;
                min-height: 100vh;
            }
            
            #sidebar {
                position: fixed !important;
                top: 0;
                right: 0;
                height: 100vh;
                overflow-y: auto;
                overflow-x: hidden;
                z-index: 40;
                transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .sidebar-expanded { width: 280px; }
            .sidebar-collapsed { width: 80px; }
            
            #main-content {
                flex: 1;
                min-height: 100vh;
                width: auto;
                margin-right: 280px;
                transition: margin-right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            #main-content.sidebar-collapsed-content {
                margin-right: 80px;
            }
            
            .mobile-overlay { display: none !important; }
        }
        
        /* 📟 Tablet (768px - 1023px) - Fixed Sidebar Layout */
        @media (min-width: 768px) and (max-width: 1023px) {
            #app {
                display: flex;
                flex-direction: row;
                min-height: 100vh;
            }
            
            #sidebar {
                position: fixed !important;
                top: 0;
                right: 0;
                height: 100vh;
                overflow-y: auto;
                overflow-x: hidden;
                z-index: 40;
                transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .sidebar-expanded { width: 260px; }
            .sidebar-collapsed { width: 70px; }
            
            #main-content {
                flex: 1;
                min-height: 100vh;
                width: auto;
                margin-right: 260px;
                transition: margin-right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            #main-content.sidebar-collapsed-content {
                margin-right: 70px;
            }
            
            .mobile-overlay { display: none !important; }
        }
        
        /* 📱 Mobile (0 - 767px) - Overlay Sidebar */
        @media (max-width: 767px) {
            #sidebar {
                position: fixed !important;
                top: 0;
                right: 0;
                height: 100vh;
                width: 280px;
                transform: translateX(100%);
                z-index: 50;
                overflow-y: auto;
                overflow-x: hidden;
            }
            
            #sidebar.mobile-visible {
                transform: translateX(0);
            }
            
            #main-content {
                margin-right: 0;
                width: 100%;
                min-height: 100vh;
            }
            
            .mobile-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 45;
                opacity: 0;
                visibility: hidden;
                transition: all 0.3s ease;
            }
            
            .mobile-overlay.active {
                opacity: 1;
                visibility: visible;
            }
        }
        
        /* 🎯 Additional Responsive Fixes */
        
        /* 🎯 Perfect Content Area System */
        * {
            box-sizing: border-box;
        }
        
        body, html {
            overflow-x: hidden;
            width: 100%;
            margin: 0;
            padding: 0;
        }
        
        /* Main App Container - Flexbox Layout */
        #app {
            min-height: 100vh;
            width: 100%;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        
        /* Desktop and Tablet Flexbox */
        @media (min-width: 768px) {
            #app {
                flex-direction: row;
            }
        }
        
        /* Navbar always on top - Fixed positioning */
        header {
            position: fixed !important;
            top: 0;
            left: 0;
            right: 0;
            z-index: 35 !important;
            width: 100%;
        }
        
        /* Content Area - Flexible & Responsive */
        #main-content {
            min-height: 100vh;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        /* Content Inner Container - Optimized Responsive Padding with fixed navbar compensation */
        .content-container {
            width: 100%;
            max-width: 100%;
            padding: 0.75rem;
            margin-top: 80px; /* Compensate for fixed navbar */
        }
        
        /* Desktop Content Padding - Reduced side margins */
        @media (min-width: 1024px) {
            .content-container {
                padding: 1.5rem 1rem;
                max-width: 1400px;
                margin: 80px auto 0 auto; /* Top margin for navbar, auto for centering */
            }
        }
        
        /* Tablet Content Padding - Optimized */
        @media (min-width: 768px) and (max-width: 1023px) {
            .content-container {
                padding: 1rem 0.75rem;
                margin-top: 80px;
            }
        }
        
        /* Mobile Content Padding - Minimal margins */
        @media (max-width: 767px) {
            .content-container {
                padding: 0.5rem 0.75rem;
                margin-top: 80px;
            }
        }
        
        /* Responsive Content Elements */
        .content-container > * {
            max-width: 100%;
        }
        
        /* Form Container Responsive */
        .form-container {
            width: 100%;
            max-width: 100%;
        }
        
        @media (min-width: 1024px) {
            .form-container {
                max-width: 1200px;
                margin: 0 auto;
            }
        }
        
        @media (min-width: 768px) and (max-width: 1023px) {
            .form-container {
                max-width: 100%;
                margin: 0;
            }
        }
        
        @media (max-width: 767px) {
            .form-container {
                max-width: 100%;
                margin: 0;
            }
        }
        
        /* RTL Support */
        [dir="rtl"] .space-x-reverse > :not([hidden]) ~ :not([hidden]) {
            --tw-space-x-reverse: 1;
            margin-right: calc(1rem * var(--tw-space-x-reverse));
            margin-left: calc(1rem * calc(1 - var(--tw-space-x-reverse)));
        }
        
        /* Dark Mode Variables */
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-sidebar: #1e293b;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --border-color: #e5e7eb;
        }
        
        [data-theme="dark"] {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-sidebar: #0f172a;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --border-color: #334155;
        }
        
        .bg-primary { background-color: var(--bg-primary); }
        .bg-secondary { background-color: var(--bg-secondary); }
        .bg-sidebar { background-color: var(--bg-sidebar); }
        .text-primary { color: var(--text-primary); }
        .text-secondary { color: var(--text-secondary); }
        .border-custom { border-color: var(--border-color); }
    </style>
</head>
<body class="bg-secondary text-primary" dir="rtl">
    <div id="app" class="min-h-screen">
        <!-- Mobile Overlay -->
        <div id="mobile-overlay" class="mobile-overlay"></div>
        
        <!-- Sidebar -->
        <aside id="sidebar" class="bg-sidebar text-white sidebar-expanded sidebar-transition shadow-lg">
            <div class="flex flex-col h-full">
                <!-- Logo Section with Toggle Button -->
                <div class="flex items-center justify-between p-4 border-b border-gray-700">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <div class="sidebar-text">
                            <h1 class="text-lg font-bold">نظام إدارة المدرسة</h1>
                            <p class="text-xs text-gray-400">الإصدار 1.0</p>
                        </div>
                    </div>
                    
                    <!-- Sidebar Toggle Button -->
                    <button id="sidebar-toggle" class="p-2 rounded-lg hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 text-white" title="طي/فرد القائمة الجانبية">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                </div>
                
                <!-- Navigation Menu -->
                <nav class="flex-1 overflow-y-auto py-4">
                    <ul class="space-y-2 px-3">
                        <!-- Dashboard -->
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center p-3 rounded-lg hover:bg-gray-700 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600' : '' }}">
                                <i class="fas fa-tachometer-alt w-5 text-center"></i>
                                <span class="sidebar-text mr-3">لوحة التحكم</span>
                            </a>
                        </li>
                        
                        <!-- Students Management -->
                        <li>
                            <div class="menu-section">
                                <button class="flex items-center w-full p-3 rounded-lg hover:bg-gray-700 transition-colors" onclick="toggleSubmenu('students-menu')">
                                    <i class="fas fa-user-graduate w-5 text-center"></i>
                                    <span class="sidebar-text mr-3 flex-1 text-right">إدارة الطلاب</span>
                                    <i class="fas fa-chevron-down sidebar-text transition-transform" id="students-arrow"></i>
                                </button>
                                <ul id="students-menu" class="hidden mt-2 mr-8 space-y-1">
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">قائمة الطلاب</a></li>
                                    <li><a href="{{ route('admin.students.create') }}" class="block p-2 rounded hover:bg-gray-700 text-sm">إضافة طالب جديد</a></li>
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">المراحل والفصول</a></li>
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">نقل الطلاب</a></li>
                                </ul>
                            </div>
                        </li>
                        
                        <!-- Teachers Management -->
                        <li>
                            <div class="menu-section">
                                <button class="flex items-center w-full p-3 rounded-lg hover:bg-gray-700 transition-colors" onclick="toggleSubmenu('teachers-menu')">
                                    <i class="fas fa-chalkboard-teacher w-5 text-center"></i>
                                    <span class="sidebar-text mr-3 flex-1 text-right">إدارة المعلمين</span>
                                    <i class="fas fa-chevron-down sidebar-text transition-transform" id="teachers-arrow"></i>
                                </button>
                                <ul id="teachers-menu" class="hidden mt-2 mr-8 space-y-1">
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">قائمة المعلمين</a></li>
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">إضافة معلم جديد</a></li>
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">جدول الحصص</a></li>
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">تقييم الأداء</a></li>
                                </ul>
                            </div>
                        </li>
                        
                        <!-- Financial Management -->
                        <li>
                            <div class="menu-section">
                                <button class="flex items-center w-full p-3 rounded-lg hover:bg-gray-700 transition-colors" onclick="toggleSubmenu('finance-menu')">
                                    <i class="fas fa-money-bill-wave w-5 text-center"></i>
                                    <span class="sidebar-text mr-3 flex-1 text-right">الإدارة المالية</span>
                                    <i class="fas fa-chevron-down sidebar-text transition-transform" id="finance-arrow"></i>
                                </button>
                                <ul id="finance-menu" class="hidden mt-2 mr-8 space-y-1">
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">المدفوعات</a></li>
                                    <li><a href="{{ route('admin.fees.records.index') }}" class="block p-2 rounded hover:bg-gray-700 text-sm">الرسوم والمصروفات</a></li>
                                    <li><a href="{{ route('admin.fees.settings.index') }}" class="block p-2 rounded hover:bg-gray-700 text-sm">إعدادات الرسوم</a></li>
                                    <li><a href="{{ route('admin.uniforms.items.index') }}" class="block p-2 rounded hover:bg-gray-700 text-sm">إدارة الزي المدرسي</a></li>
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">التقارير المالية</a></li>
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">المتأخرات</a></li>
                                </ul>
                            </div>
                        </li>
                        
                        <!-- Academic Management -->
                        <li>
                            <div class="menu-section">
                                <button class="flex items-center w-full p-3 rounded-lg hover:bg-gray-700 transition-colors" onclick="toggleSubmenu('academic-menu')">
                                    <i class="fas fa-book w-5 text-center"></i>
                                    <span class="sidebar-text mr-3 flex-1 text-right">الإدارة الأكاديمية</span>
                                    <i class="fas fa-chevron-down sidebar-text transition-transform" id="academic-arrow"></i>
                                </button>
                                <ul id="academic-menu" class="hidden mt-2 mr-8 space-y-1">
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">المواد الدراسية</a></li>
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">الحضور والغياب</a></li>
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">الامتحانات</a></li>
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">الدرجات</a></li>
                                </ul>
                            </div>
                        </li>
                        
                        <!-- Communications -->
                        <li>
                            <div class="menu-section">
                                <button class="flex items-center w-full p-3 rounded-lg hover:bg-gray-700 transition-colors" onclick="toggleSubmenu('comm-menu')">
                                    <i class="fas fa-comments w-5 text-center"></i>
                                    <span class="sidebar-text mr-3 flex-1 text-right">التواصل</span>
                                    <i class="fas fa-chevron-down sidebar-text transition-transform" id="comm-arrow"></i>
                                </button>
                                <ul id="comm-menu" class="hidden mt-2 mr-8 space-y-1">
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">الإشعارات</a></li>
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">الرسائل الجماعية</a></li>
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">التقارير للأولياء</a></li>
                                </ul>
                            </div>
                        </li>
                        
                        <!-- Reports -->
                        <li>
                            <a href="#" class="flex items-center p-3 rounded-lg hover:bg-gray-700 transition-colors">
                                <i class="fas fa-chart-bar w-5 text-center"></i>
                                <span class="sidebar-text mr-3">التقارير</span>
                            </a>
                        </li>
                        
                        <!-- Settings -->
                        <li>
                            <div class="menu-section">
                                <button class="flex items-center w-full p-3 rounded-lg hover:bg-gray-700 transition-colors" onclick="toggleSubmenu('settings-menu')">
                                    <i class="fas fa-cog w-5 text-center"></i>
                                    <span class="sidebar-text mr-3 flex-1 text-right">الإعدادات</span>
                                    <i class="fas fa-chevron-down sidebar-text transition-transform" id="settings-arrow"></i>
                                </button>
                                <ul id="settings-menu" class="hidden mt-2 mr-8 space-y-1">
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">إدارة المستخدمين</a></li>
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">إعدادات النظام</a></li>
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">النسخ الاحتياطي</a></li>
                                    <li><a href="#" class="block p-2 rounded hover:bg-gray-700 text-sm">سجلات النظام</a></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </nav>
                
                <!-- User Profile Section -->
                <div class="border-t border-gray-700 p-4">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name ?? 'مدير' }}&background=3b82f6&color=fff&size=40" 
                             alt="صورة المستخدم" class="w-10 h-10 rounded-full">
                        <div class="sidebar-text flex-1">
                            <p class="text-sm font-medium">{{ auth()->user()->name ?? 'مدير النظام' }}</p>
                            <p class="text-xs text-gray-400">{{ auth()->user()->role ?? 'مدير' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main id="main-content" class="content-transition">
            <!-- Top Navbar -->
            <header class="bg-primary border-b border-custom shadow-sm sticky top-0 z-30">
                <div class="flex items-center justify-between px-6 py-4">
                    <!-- Left Side -->
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <!-- Breadcrumb -->
                        <nav class="flex" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-2 space-x-reverse text-sm">
                                <li><a href="#" class="text-blue-600 hover:text-blue-800">الرئيسية</a></li>
                                <li><i class="fas fa-chevron-left text-gray-400 mx-2"></i></li>
                                <li class="text-gray-500">@yield('page-title', 'لوحة التحكم')</li>
                            </ol>
                        </nav>
                    </div>
                    
                    <!-- Center - Search -->
                    <div class="flex-1 max-w-md mx-4 hidden md:block">
                        <div class="relative">
                            <input type="text" id="global-search" 
                                   placeholder="البحث في النظام..." 
                                   class="w-full px-4 py-2 pr-10 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>
                        <!-- Search Results Dropdown -->
                        <div id="search-results" class="absolute top-full left-0 right-0 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg mt-1 hidden z-50">
                            <!-- Search results will be populated here -->
                        </div>
                    </div>
                    
                    <!-- Right Side -->
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <!-- Mobile Sidebar Toggle -->
                        <button id="mobile-sidebar-toggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors md:hidden" title="فتح القائمة الجانبية">
                            <i class="fas fa-bars text-gray-600 dark:text-gray-300 text-lg"></i>
                        </button>
                        
                        <!-- Mobile Search Toggle -->
                        <button id="mobile-search-toggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors md:hidden">
                            <i class="fas fa-search text-gray-600 dark:text-gray-300"></i>
                        </button>
                        
                        <!-- Dark Mode Toggle -->
                        <button id="theme-toggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <i class="fas fa-moon text-gray-600 dark:text-gray-300" id="theme-icon"></i>
                        </button>
                        
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors relative">
                                <i class="fas fa-bell text-gray-600 dark:text-gray-300"></i>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
                            </button>
                        </div>
                        
                        <!-- User Menu -->
                        <div class="relative">
                            <button class="flex items-center space-x-2 space-x-reverse p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <img src="https://ui-avatars.com/api/?name={{ auth()->user()->name ?? 'مدير' }}&background=3b82f6&color=fff&size=32" 
                                     alt="صورة المستخدم" class="w-8 h-8 rounded-full">
                                <span class="hidden md:block text-sm font-medium">{{ auth()->user()->name ?? 'مدير النظام' }}</span>
                                <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile Search Bar -->
                <div id="mobile-search-bar" class="hidden md:hidden px-6 py-3 border-t border-custom">
                    <div class="relative">
                        <input type="text" id="mobile-global-search" 
                               placeholder="البحث في النظام..." 
                               class="w-full px-4 py-2 pr-10 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <i class="fas fa-search absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <div class="content-container">
                @yield('content')
            </div>
        </main>
    </div>
    
    <!-- JavaScript -->
    <script>
        // 🚀 Professional Responsive Sidebar System
        class ResponsiveSidebar {
            constructor() {
                this.sidebar = document.getElementById('sidebar');
                this.mainContent = document.getElementById('main-content');
                this.sidebarToggle = document.getElementById('sidebar-toggle');
                this.sidebarTexts = document.querySelectorAll('.sidebar-text');
                this.mobileOverlay = document.getElementById('mobile-overlay');
                
                this.sidebarExpanded = true;
                this.init();
            }
            
            // Get current breakpoint
            getBreakpoint() {
                const width = window.innerWidth;
                if (width >= 1024) return 'desktop';
                if (width >= 768) return 'tablet';
                return 'mobile';
            }
            
            // Initialize system
            init() {
                this.updateSidebarState();
                this.attachEvents();
                console.log('🚀 Responsive Sidebar initialized');
            }
            
            // Toggle sidebar
            toggle() {
                const breakpoint = this.getBreakpoint();
                
                if (breakpoint === 'mobile') {
                    this.toggleMobile();
                } else {
                    this.toggleDesktop();
                }
            }
            
            // Toggle mobile sidebar
            toggleMobile() {
                const isVisible = this.sidebar.classList.contains('mobile-visible');
                if (isVisible) {
                    this.closeMobile();
                } else {
                    this.openMobile();
                }
            }
            
            // Toggle desktop sidebar
            toggleDesktop() {
                this.sidebarExpanded = !this.sidebarExpanded;
                this.updateSidebarState();
                localStorage.setItem('sidebarExpanded', this.sidebarExpanded);
            }
            
            // Open mobile sidebar
            openMobile() {
                this.sidebar.classList.add('mobile-visible');
                this.mobileOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
            
            // Close mobile sidebar
            closeMobile() {
                this.sidebar.classList.remove('mobile-visible');
                this.mobileOverlay.classList.remove('active');
                document.body.style.overflow = 'auto';
            }
            
            // Update sidebar state
            updateSidebarState() {
                const breakpoint = this.getBreakpoint();
                
                // Clear classes
                this.sidebar.classList.remove('sidebar-expanded', 'sidebar-collapsed');
                this.mainContent.classList.remove('sidebar-collapsed-content');
                
                if (breakpoint === 'mobile') {
                    // Mobile: Always expanded when visible
                    this.sidebar.classList.add('sidebar-expanded');
                    this.showTexts();
                } else {
                    // Desktop/Tablet: Collapsible
                    if (this.sidebarExpanded) {
                        this.sidebar.classList.add('sidebar-expanded');
                        this.showTexts();
                    } else {
                        this.sidebar.classList.add('sidebar-collapsed');
                        this.mainContent.classList.add('sidebar-collapsed-content');
                        this.hideTexts();
                    }
                }
            }
            
            // Show/hide sidebar texts
            showTexts() {
                this.sidebarTexts.forEach(text => text.style.display = 'block');
            }
            
            hideTexts() {
                this.sidebarTexts.forEach(text => text.style.display = 'none');
            }
            
            // Handle resize
            handleResize() {
                const newBreakpoint = this.getBreakpoint();
                
                // Close mobile sidebar when switching to desktop/tablet
                if (newBreakpoint !== 'mobile') {
                    this.closeMobile();
                }
                
                this.updateSidebarState();
            }
            
            // Attach events
            attachEvents() {
                this.sidebarToggle?.addEventListener('click', () => this.toggle());
                this.mobileOverlay?.addEventListener('click', () => this.closeMobile());
                
                // Escape key
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && this.getBreakpoint() === 'mobile') {
                        this.closeMobile();
                    }
                });
                
                // Resize
                window.addEventListener('resize', () => this.handleResize());
                
                // Load saved state
                const saved = localStorage.getItem('sidebarExpanded');
                if (saved !== null) {
                    this.sidebarExpanded = saved === 'true';
                    this.updateSidebarState();
                }
            }
        }
        
        // Initialize
        const responsiveSidebar = new ResponsiveSidebar();
        
        // Mobile Sidebar Toggle (separate button for mobile)
        const mobileSidebarToggle = document.getElementById('mobile-sidebar-toggle');
        if (mobileSidebarToggle) {
            mobileSidebarToggle.addEventListener('click', () => responsiveSidebar.toggle());
        }
        
        // Mobile Search Toggle
        const mobileSearchToggle = document.getElementById('mobile-search-toggle');
        const mobileSearchBar = document.getElementById('mobile-search-bar');
        
        if (mobileSearchToggle) {
            mobileSearchToggle.addEventListener('click', function() {
                if (mobileSearchBar.classList.contains('hidden')) {
                    mobileSearchBar.classList.remove('hidden');
                    document.getElementById('mobile-global-search').focus();
                } else {
                    mobileSearchBar.classList.add('hidden');
                }
            });
        }
        
        // Submenu Toggle
        function toggleSubmenu(menuId) {
            const menu = document.getElementById(menuId);
            const arrow = document.getElementById(menuId.replace('-menu', '-arrow'));
            
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                arrow.style.transform = 'rotate(180deg)';
            } else {
                menu.classList.add('hidden');
                arrow.style.transform = 'rotate(0deg)';
            }
        }
        
        // Dark Mode Toggle
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = document.getElementById('theme-icon');
        const html = document.documentElement;
        
        // Check for saved theme preference or default to light mode
        const currentTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', currentTheme);
        
        if (currentTheme === 'dark') {
            themeIcon.classList.remove('fa-moon');
            themeIcon.classList.add('fa-sun');
        }
        
        themeToggle.addEventListener('click', function() {
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            if (newTheme === 'dark') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
        });
        
        // Global Search
        const globalSearch = document.getElementById('global-search');
        const searchResults = document.getElementById('search-results');
        
        globalSearch.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (query.length > 2) {
                // Simulate search results
                searchResults.innerHTML = `
                    <div class="p-4">
                        <div class="text-sm text-gray-500 mb-2">نتائج البحث عن: "${query}"</div>
                        <div class="space-y-2">
                            <a href="#" class="block p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                                <div class="font-medium">أحمد محمد علي</div>
                                <div class="text-sm text-gray-500">طالب - الصف الثالث الابتدائي</div>
                            </a>
                            <a href="#" class="block p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded">
                                <div class="font-medium">فاطمة أحمد</div>
                                <div class="text-sm text-gray-500">معلمة - اللغة العربية</div>
                            </a>
                        </div>
                    </div>
                `;
                searchResults.classList.remove('hidden');
            } else {
                searchResults.classList.add('hidden');
            }
        });
        
        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!globalSearch.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });
        
        // These functions are now handled by the ResponsiveSidebar class above
        // No need for duplicate code

    </script>

    <!-- Page Scripts -->
    @yield('scripts')
</body>
</html>