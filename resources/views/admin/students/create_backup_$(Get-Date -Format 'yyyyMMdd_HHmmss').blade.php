@extends('layouts.admin')

@section('title', 'إضافة طالب جديد')
@section('page-title', 'إضافة طالب جديد')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-full mb-4">
                <i class="fas fa-user-plus text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">إضافة طالب جديد</h1>
            <p class="text-gray-600">املأ البيانات الشخصية للطالب بدقة</p>
        </div>





        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 ml-2"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        <!-- Error Message -->
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 ml-2"></i>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Form Container -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Form Header -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-8 py-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-id-card text-white text-2xl"></i>
                    </div>
                    <div class="mr-4">
                        <h2 class="text-xl font-bold text-white">القسم الأول: البيانات الشخصية</h2>
                        <p class="text-blue-100 text-sm">المعلومات الأساسية للطالب</p>
                    </div>
                </div>
            </div>

            <!-- Form Body -->
            <form action="{{ route('admin.students.store') }}" method="POST" class="p-8" id="studentForm">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- الاسم الكامل -->
                    <div class="md:col-span-2">
                        <label for="full_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user text-blue-600 ml-2"></i>
                            الاسم الكامل بالعربية (رباعي)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="full_name" 
                               name="full_name" 
                               required
                               placeholder="مثال: أحمد محمد علي حسن"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 text-right"
                               value="{{ old('full_name') }}">

                    </div>

                    <!-- الرقم القومي -->
                    <div>
                        <label for="national_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-id-badge text-blue-600 ml-2"></i>
                            الرقم القومي (14 رقم)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="national_id" 
                               name="national_id" 
                               required
                               maxlength="14"
                               pattern="[0-9]{14}"
                               placeholder="12345678901234"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                               value="{{ old('national_id') }}">
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle text-blue-500"></i>
                            سيتم استخدامه لتسجيل الدخول
                        </p>

                    </div>

                    <!-- تاريخ الميلاد -->
                    <div>
                        <label for="birth_date" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt text-blue-600 ml-2"></i>
                            تاريخ الميلاد
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="birth_date" 
                               name="birth_date" 
                               required
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                               value="{{ old('birth_date') }}">

                    </div>

                    <!-- مكان الميلاد -->
                    <div>
                        <label for="birth_place" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt text-blue-600 ml-2"></i>
                            مكان الميلاد
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="birth_place" 
                               name="birth_place" 
                               required
                               placeholder="مثال: القاهرة"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 text-right"
                               value="{{ old('birth_place') }}">

                    </div>

                    <!-- الجنسية -->
                    <div>
                        <label for="nationality" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-flag text-blue-600 ml-2"></i>
                            الجنسية
                            <span class="text-red-500">*</span>
                        </label>
                        <select id="nationality" 
                                name="nationality" 
                                required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            <option value="">اختر الجنسية</option>
                            <option value="مصرية" {{ old('nationality') == 'مصرية' ? 'selected' : '' }}>مصرية</option>
                            <option value="سعودية" {{ old('nationality') == 'سعودية' ? 'selected' : '' }}>سعودية</option>
                            <option value="إماراتية" {{ old('nationality') == 'إماراتية' ? 'selected' : '' }}>إماراتية</option>
                            <option value="أخرى" {{ old('nationality') == 'أخرى' ? 'selected' : '' }}>أخرى</option>
                        </select>

                    </div>

                    <!-- النوع -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-venus-mars text-blue-600 ml-2"></i>
                            النوع
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="flex space-x-4 space-x-reverse">
                            <label class="flex items-center">
                                <input type="radio" 
                                       name="gender" 
                                       value="ذكر" 
                                       required
                                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                       {{ old('gender') == 'ذكر' ? 'checked' : '' }}>
                                <span class="mr-2 text-gray-700">ذكر</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" 
                                       name="gender" 
                                       value="أنثى" 
                                       required
                                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                       {{ old('gender') == 'أنثى' ? 'checked' : '' }}>
                                <span class="mr-2 text-gray-700">أنثى</span>
                            </label>
                        </div>

                    </div>

                    <!-- الديانة -->
                    <div>
                        <label for="religion" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-pray text-blue-600 ml-2"></i>
                            الديانة
                            <span class="text-red-500">*</span>
                        </label>
                        <select id="religion" 
                                name="religion" 
                                required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            <option value="">اختر الديانة</option>
                            <option value="مسلم" {{ old('religion') == 'مسلم' ? 'selected' : '' }}>مسلم</option>
                            <option value="مسيحي" {{ old('religion') == 'مسيحي' ? 'selected' : '' }}>مسيحي</option>
                            <option value="أخرى" {{ old('religion') == 'أخرى' ? 'selected' : '' }}>أخرى</option>
                        </select>

                    </div>

                    <!-- العنوان -->
                    <div class="md:col-span-2">
                        <label for="address" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-home text-blue-600 ml-2"></i>
                            عنوان الطالب
                            <span class="text-red-500">*</span>
                        </label>
                        <textarea id="address" 
                                  name="address" 
                                  required
                                  rows="3"
                                  placeholder="العنوان التفصيلي للطالب"
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 text-right resize-none">{{ old('address') }}</textarea>

                    </div>

                    <!-- الاحتياجات الخاصة -->
                    <div class="md:col-span-2">
                        <label for="special_needs" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-wheelchair text-blue-600 ml-2"></i>
                            الاحتياجات الخاصة (اختياري)
                        </label>
                        <textarea id="special_needs" 
                                  name="special_needs" 
                                  rows="3"
                                  placeholder="اذكر أي احتياجات خاصة للطالب (إن وجدت)"
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 text-right resize-none">{{ old('special_needs') }}</textarea>

                    </div>

                    <!-- ملاحظات -->
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-sticky-note text-blue-600 ml-2"></i>
                            ملاحظات إضافية (اختياري)
                        </label>
                        <textarea id="notes" 
                                  name="notes" 
                                  rows="3"
                                  placeholder="أي ملاحظات إضافية حول الطالب"
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 text-right resize-none">{{ old('notes') }}</textarea>

                    </div>
                </div>
            </div>

            <!-- القسم الثاني: البيانات الأكاديمية -->
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-8 py-6">
                <div class="flex items-center">
                    <div class="bg-white bg-opacity-20 rounded-full p-3 ml-4">
                        <i class="fas fa-graduation-cap text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">البيانات الأكاديمية</h2>
                        <p class="text-green-100 text-sm">معلومات الصف والمنهج والمستوى الأكاديمي</p>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- العام الدراسي -->
                    <div>
                        <label for="academic_year" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt text-blue-500 ml-2"></i>
                            العام الدراسي
                        </label>
                        <select id="academic_year" name="academic_year" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            <option value="">اختر العام الدراسي</option>
                            <option value="2024-2025">2024-2025</option>
                            <option value="2025-2026">2025-2026</option>
                        </select>
                    </div>

                    <!-- المرحلة الدراسية -->
                    <div>
                        <label for="grade_level" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-layer-group text-green-500 ml-2"></i>
                            المرحلة الدراسية
                        </label>
                        <select id="grade_level" name="grade_level" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                onchange="updateGrades()">
                            <option value="">اختر المرحلة الدراسية</option>
                            <option value="primary">المرحلة الابتدائية</option>
                            <option value="preparatory">المرحلة الإعدادية</option>
                        </select>
                    </div>

                    <!-- الصف الدراسي -->
                    <div>
                        <label for="grade" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-book text-purple-500 ml-2"></i>
                            الصف الدراسي
                        </label>
                        <select id="grade" name="grade" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                onchange="updateClassrooms()" disabled>
                            <option value="">اختر الصف الدراسي</option>
                        </select>
                    </div>

                    <!-- الفصل -->
                    <div>
                        <label for="classroom" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-door-open text-orange-500 ml-2"></i>
                            الفصل
                        </label>
                        <select id="classroom" name="classroom" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                disabled>
                            <option value="">اختر الفصل</option>
                        </select>
                    </div>

                    <!-- نوع القيد -->
                    <div>
                        <label for="enrollment_type" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user-plus text-indigo-500 ml-2"></i>
                            نوع القيد
                        </label>
                        <select id="enrollment_type" name="enrollment_type" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                                onchange="togglePreviousSchoolFields()">
                            <option value="">اختر نوع القيد</option>
                            <option value="new">مستجد</option>
                            <option value="transfer">تحويل</option>
                            <option value="return">عائد من سفر</option>
                        </select>
                    </div>

                    <!-- تاريخ الالتحاق بالمدرسة -->
                    <div>
                        <label for="enrollment_date" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar-check text-teal-500 ml-2"></i>
                            تاريخ الالتحاق بالمدرسة
                        </label>
                        <input type="date"
                               id="enrollment_date"
                               name="enrollment_date"
                               required
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                    </div>

                    <!-- اسم المدرسة السابقة (ديناميكي) -->
                    <div id="previous_school_field" class="hidden">
                        <label for="previous_school" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-school text-red-500 ml-2"></i>
                            اسم المدرسة السابقة
                        </label>
                        <input type="text"
                               id="previous_school"
                               name="previous_school"
                               placeholder="اسم المدرسة السابقة"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 text-right">
                    </div>

                    <!-- سبب التحويل (ديناميكي) -->
                    <div id="transfer_reason_field" class="hidden">
                        <label for="transfer_reason" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-question-circle text-yellow-500 ml-2"></i>
                            سبب التحويل
                        </label>
                        <textarea id="transfer_reason"
                                  name="transfer_reason"
                                  rows="3"
                                  placeholder="اذكر سبب التحويل"
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200 text-right resize-none"></textarea>
                    </div>

                    <!-- مستوى الطالب السابق -->
                    <div>
                        <label for="previous_level" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-chart-line text-pink-500 ml-2"></i>
                            مستوى الطالب السابق
                        </label>
                        <select id="previous_level" name="previous_level" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            <option value="">اختر المستوى</option>
                            <option value="excellent">تفوق</option>
                            <option value="good">جيد</option>
                            <option value="needs_support">يحتاج دعم</option>
                        </select>
                    </div>

                    <!-- اللغة الثانية -->
                    <div>
                        <label for="second_language" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-language text-cyan-500 ml-2"></i>
                            اللغة الثانية
                        </label>
                        <select id="second_language" name="second_language" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            <option value="">اختر اللغة الثانية</option>
                            <option value="french">فرنسي</option>
                            <option value="german">ألماني</option>
                            <option value="italian">إيطالي</option>
                        </select>
                    </div>

                    <!-- نوع المنهج -->
                    <div>
                        <label for="curriculum_type" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-book-open text-emerald-500 ml-2"></i>
                            نوع المنهج
                        </label>
                        <select id="curriculum_type" name="curriculum_type" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            <option value="">اختر نوع المنهج</option>
                            <option value="national">وطني</option>
                            <option value="international">دولي</option>
                            <option value="languages">لغات</option>
                        </select>
                    </div>

                    <!-- هل سبق للطالب الرسوب؟ -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-exclamation-triangle text-red-500 ml-2"></i>
                            هل سبق للطالب الرسوب؟
                        </label>
                        <div class="flex gap-6 mt-3">
                            <label class="flex items-center">
                                <input type="radio"
                                       name="has_failed"
                                       value="no"
                                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                       checked>
                                <span class="mr-2 text-gray-700">لا</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio"
                                       name="has_failed"
                                       value="yes"
                                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                <span class="mr-2 text-gray-700">نعم</span>
                            </label>
                        </div>
                    </div>

                    <!-- ترتيب الطالب بين إخوته -->
                    <div>
                        <label for="sibling_order" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-users text-violet-500 ml-2"></i>
                            ترتيب الطالب بين إخوته في نفس المدرسة
                        </label>
                        <select id="sibling_order" name="sibling_order" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200">
                            <option value="">اختر الترتيب</option>
                            <option value="first">الأول</option>
                            <option value="second">الثاني</option>
                            <option value="third">الثالث</option>
                            <option value="fourth">الرابع</option>
                            <option value="fifth">الخامس</option>
                            <option value="other">أخرى</option>
                        </select>
                    </div>

                    <!-- هل الطالب منتظم أم مستمع؟ -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user-check text-lime-500 ml-2"></i>
                            هل الطالب منتظم أم مستمع؟
                        </label>
                        <div class="flex gap-6 mt-3">
                            <label class="flex items-center">
                                <input type="radio"
                                       name="attendance_type"
                                       value="regular"
                                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                       checked>
                                <span class="mr-2 text-gray-700">منتظم</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio"
                                       name="attendance_type"
                                       value="listener"
                                       class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                <span class="mr-2 text-gray-700">مستمع</span>
                            </label>
                        </div>
                    </div>
                </div>

            <!-- القسم الثالث: بيانات ولي الأمر -->
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-6">
                <div class="flex items-center">
                    <div class="bg-white bg-opacity-20 rounded-full p-3 ml-4">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">بيانات ولي الأمر</h2>
                        <p class="text-purple-100 text-sm">معلومات ولي الأمر والوصي القانوني</p>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- اسم ولي الأمر الكامل -->
                    <div class="md:col-span-2">
                        <label for="guardian_full_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user text-purple-600 ml-2"></i>
                            الاسم الكامل لولي الأمر
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="guardian_full_name" 
                               name="guardian_full_name" 
                               required
                               placeholder="مثال: محمد أحمد علي حسن"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200 text-right"
                               value="{{ old('guardian_full_name') }}">
                        @error('guardian_full_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- صلة القرابة -->
                    <div>
                        <label for="guardian_relationship" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-heart text-purple-600 ml-2"></i>
                            صلة القرابة
                            <span class="text-red-500">*</span>
                        </label>
                        <select id="guardian_relationship" 
                                name="guardian_relationship" 
                                required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200">
                            <option value="">اختر صلة القرابة</option>
                            <option value="الأب" {{ old('guardian_relationship') == 'الأب' ? 'selected' : '' }}>الأب</option>
                            <option value="الأم" {{ old('guardian_relationship') == 'الأم' ? 'selected' : '' }}>الأم</option>
                            <option value="الجد" {{ old('guardian_relationship') == 'الجد' ? 'selected' : '' }}>الجد</option>
                            <option value="الجدة" {{ old('guardian_relationship') == 'الجدة' ? 'selected' : '' }}>الجدة</option>
                            <option value="العم" {{ old('guardian_relationship') == 'العم' ? 'selected' : '' }}>العم</option>
                            <option value="العمة" {{ old('guardian_relationship') == 'العمة' ? 'selected' : '' }}>العمة</option>
                            <option value="الخال" {{ old('guardian_relationship') == 'الخال' ? 'selected' : '' }}>الخال</option>
                            <option value="الخالة" {{ old('guardian_relationship') == 'الخالة' ? 'selected' : '' }}>الخالة</option>
                            <option value="وصي قانوني" {{ old('guardian_relationship') == 'وصي قانوني' ? 'selected' : '' }}>وصي قانوني</option>
                        </select>
                        @error('guardian_relationship')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- الرقم القومي لولي الأمر -->
                    <div>
                        <label for="guardian_national_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-id-badge text-purple-600 ml-2"></i>
                            الرقم القومي لولي الأمر (14 رقم)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="guardian_national_id" 
                               name="guardian_national_id" 
                               required
                               maxlength="14"
                               pattern="[0-9]{14}"
                               placeholder="12345678901234"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200"
                               value="{{ old('guardian_national_id') }}">
                        @error('guardian_national_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- الوظيفة -->
                    <div>
                        <label for="guardian_job_title" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-briefcase text-purple-600 ml-2"></i>
                            الوظيفة
                        </label>
                        <input type="text" 
                               id="guardian_job_title" 
                               name="guardian_job_title" 
                               placeholder="مثال: مهندس"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200 text-right"
                               value="{{ old('guardian_job_title') }}">
                    </div>

                    <!-- جهة العمل -->
                    <div>
                        <label for="guardian_workplace" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-building text-purple-600 ml-2"></i>
                            جهة العمل
                        </label>
                        <input type="text" 
                               id="guardian_workplace" 
                               name="guardian_workplace" 
                               placeholder="مثال: شركة المقاولون العرب"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200 text-right"
                               value="{{ old('guardian_workplace') }}">
                    </div>

                    <!-- المؤهل الدراسي -->
                    <div>
                        <label for="guardian_education_level" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-graduation-cap text-purple-600 ml-2"></i>
                            المؤهل الدراسي
                        </label>
                        <select id="guardian_education_level" 
                                name="guardian_education_level" 
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200">
                            <option value="">اختر المؤهل الدراسي</option>
                            <option value="ابتدائية" {{ old('guardian_education_level') == 'ابتدائية' ? 'selected' : '' }}>ابتدائية</option>
                            <option value="إعدادية" {{ old('guardian_education_level') == 'إعدادية' ? 'selected' : '' }}>إعدادية</option>
                            <option value="ثانوية عامة" {{ old('guardian_education_level') == 'ثانوية عامة' ? 'selected' : '' }}>ثانوية عامة</option>
                            <option value="ثانوية فنية" {{ old('guardian_education_level') == 'ثانوية فنية' ? 'selected' : '' }}>ثانوية فنية</option>
                            <option value="دبلوم" {{ old('guardian_education_level') == 'دبلوم' ? 'selected' : '' }}>دبلوم</option>
                            <option value="بكالوريوس" {{ old('guardian_education_level') == 'بكالوريوس' ? 'selected' : '' }}>بكالوريوس</option>
                            <option value="ماجستير" {{ old('guardian_education_level') == 'ماجستير' ? 'selected' : '' }}>ماجستير</option>
                            <option value="دكتوراه" {{ old('guardian_education_level') == 'دكتوراه' ? 'selected' : '' }}>دكتوراه</option>
                        </select>
                    </div>

                    <!-- رقم الهاتف المحمول -->
                    <div>
                        <label for="guardian_mobile_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-mobile-alt text-purple-600 ml-2"></i>
                            رقم الهاتف المحمول
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" 
                               id="guardian_mobile_phone" 
                               name="guardian_mobile_phone" 
                               required
                               placeholder="01012345678"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200"
                               value="{{ old('guardian_mobile_phone') }}">
                        @error('guardian_mobile_phone')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- رقم هاتف آخر (احتياطي) -->
                    <div>
                        <label for="guardian_alternative_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-phone text-purple-600 ml-2"></i>
                            رقم هاتف آخر (احتياطي)
                        </label>
                        <input type="tel" 
                               id="guardian_alternative_phone" 
                               name="guardian_alternative_phone" 
                               placeholder="01012345678"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200"
                               value="{{ old('guardian_alternative_phone') }}">
                    </div>

                    <!-- البريد الإلكتروني -->
                    <div>
                        <label for="guardian_email" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-envelope text-purple-600 ml-2"></i>
                            البريد الإلكتروني
                        </label>
                        <input type="email" 
                               id="guardian_email" 
                               name="guardian_email" 
                               placeholder="example@email.com"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200"
                               value="{{ old('guardian_email') }}">
                        @error('guardian_email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- الحالة الاجتماعية -->
                    <div>
                        <label for="guardian_marital_status" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-ring text-purple-600 ml-2"></i>
                            الحالة الاجتماعية
                        </label>
                        <select id="guardian_marital_status" 
                                name="guardian_marital_status" 
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200">
                            <option value="">اختر الحالة الاجتماعية</option>
                            <option value="أعزب" {{ old('guardian_marital_status') == 'أعزب' ? 'selected' : '' }}>أعزب</option>
                            <option value="متزوج" {{ old('guardian_marital_status') == 'متزوج' ? 'selected' : '' }}>متزوج</option>
                            <option value="مطلق" {{ old('guardian_marital_status') == 'مطلق' ? 'selected' : '' }}>مطلق</option>
                            <option value="أرمل" {{ old('guardian_marital_status') == 'أرمل' ? 'selected' : '' }}>أرمل</option>
                        </select>
                    </div>

                    <!-- عنوان السكن -->
                    <div class="md:col-span-2">
                        <label for="guardian_address" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-home text-purple-600 ml-2"></i>
                            عنوان السكن
                            <span class="text-red-500">*</span>
                        </label>
                        <textarea id="guardian_address" 
                                  name="guardian_address" 
                                  required
                                  rows="3"
                                  placeholder="العنوان التفصيلي لولي الأمر"
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200 text-right resize-none">{{ old('guardian_address') }}</textarea>
                        @error('guardian_address')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- هل يوجد وصي قانوني؟ -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-gavel text-purple-600 ml-2"></i>
                            هل يوجد وصي قانوني؟
                        </label>
                        <div class="flex space-x-4 space-x-reverse">
                            <label class="flex items-center">
                                <input type="radio" 
                                       name="has_legal_guardian" 
                                       value="1" 
                                       id="has_legal_guardian_yes"
                                       class="w-4 h-4 text-purple-600 border-gray-300 focus:ring-purple-500"
                                       {{ old('has_legal_guardian') == '1' ? 'checked' : '' }}
                                       onchange="toggleLegalGuardianFields()">
                                <span class="mr-2 text-gray-700">نعم</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" 
                                       name="has_legal_guardian" 
                                       value="0" 
                                       id="has_legal_guardian_no"
                                       class="w-4 h-4 text-purple-600 border-gray-300 focus:ring-purple-500"
                                       {{ old('has_legal_guardian') == '0' || old('has_legal_guardian') === null ? 'checked' : '' }}
                                       onchange="toggleLegalGuardianFields()">
                                <span class="mr-2 text-gray-700">لا</span>
                            </label>
                        </div>
                    </div>

                    <!-- بيانات الوصي القانوني (ديناميكية) -->
                    <div id="legal_guardian_fields" class="md:col-span-2 hidden">
                        <div class="bg-gray-50 p-6 rounded-lg border-2 border-dashed border-gray-300">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                                <i class="fas fa-user-tie text-purple-600 ml-2"></i>
                                بيانات الوصي القانوني
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- اسم الوصي القانوني -->
                                <div>
                                    <label for="legal_guardian_full_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                        الاسم الكامل للوصي القانوني
                                    </label>
                                    <input type="text" 
                                           id="legal_guardian_full_name" 
                                           name="legal_guardian_full_name" 
                                           placeholder="اسم الوصي القانوني"
                                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200 text-right"
                                           value="{{ old('legal_guardian_full_name') }}">
                                </div>

                                <!-- الرقم القومي للوصي -->
                                <div>
                                    <label for="legal_guardian_national_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                        الرقم القومي للوصي (14 رقم)
                                    </label>
                                    <input type="text" 
                                           id="legal_guardian_national_id" 
                                           name="legal_guardian_national_id" 
                                           maxlength="14"
                                           pattern="[0-9]{14}"
                                           placeholder="12345678901234"
                                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200"
                                           value="{{ old('legal_guardian_national_id') }}">
                                </div>

                                <!-- صلة القرابة للوصي -->
                                <div>
                                    <label for="legal_guardian_relationship" class="block text-sm font-semibold text-gray-700 mb-2">
                                        صلة القرابة
                                    </label>
                                    <input type="text" 
                                           id="legal_guardian_relationship" 
                                           name="legal_guardian_relationship" 
                                           placeholder="مثال: العم"
                                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200 text-right"
                                           value="{{ old('legal_guardian_relationship') }}">
                                </div>

                                <!-- رقم هاتف الوصي -->
                                <div>
                                    <label for="legal_guardian_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                        رقم الهاتف
                                    </label>
                                    <input type="tel" 
                                           id="legal_guardian_phone" 
                                           name="legal_guardian_phone" 
                                           placeholder="01012345678"
                                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200"
                                           value="{{ old('legal_guardian_phone') }}">
                                </div>

                                <!-- عنوان الوصي -->
                                <div class="md:col-span-2">
                                    <label for="legal_guardian_address" class="block text-sm font-semibold text-gray-700 mb-2">
                                        العنوان
                                    </label>
                                    <textarea id="legal_guardian_address" 
                                              name="legal_guardian_address" 
                                              rows="2"
                                              placeholder="عنوان الوصي القانوني"
                                              class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200 text-right resize-none">{{ old('legal_guardian_address') }}</textarea>
                                </div>

                                <!-- رقم الوثيقة القانونية -->
                                <div>
                                    <label for="legal_guardian_document_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                        رقم الوثيقة القانونية
                                    </label>
                                    <input type="text" 
                                           id="legal_guardian_document_number" 
                                           name="legal_guardian_document_number" 
                                           placeholder="رقم الوثيقة"
                                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200"
                                           value="{{ old('legal_guardian_document_number') }}">
                                </div>

                                <!-- تفاصيل الوثيقة -->
                                <div>
                                    <label for="legal_guardian_document_details" class="block text-sm font-semibold text-gray-700 mb-2">
                                        تفاصيل الوثيقة القانونية
                                    </label>
                                    <textarea id="legal_guardian_document_details" 
                                              name="legal_guardian_document_details" 
                                              rows="2"
                                              placeholder="تفاصيل الوثيقة القانونية"
                                              class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all duration-200 text-right resize-none">{{ old('legal_guardian_document_details') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- حسابات التواصل الاجتماعي (اختياري) -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-share-alt text-purple-600 ml-2"></i>
                            حسابات التواصل الاجتماعي (اختياري)
                        </label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="facebook_account" class="block text-xs text-gray-600 mb-1">فيسبوك</label>
                                <input type="text" 
                                       id="facebook_account" 
                                       name="guardian_social_media[facebook]" 
                                       placeholder="رابط الفيسبوك"
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:border-purple-500 focus:ring-1 focus:ring-purple-200 transition-all duration-200"
                                       value="{{ old('guardian_social_media.facebook') }}">
                            </div>
                            <div>
                                <label for="whatsapp_account" class="block text-xs text-gray-600 mb-1">واتساب</label>
                                <input type="text" 
                                       id="whatsapp_account" 
                                       name="guardian_social_media[whatsapp]" 
                                       placeholder="رقم الواتساب"
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:border-purple-500 focus:ring-1 focus:ring-purple-200 transition-all duration-200"
                                       value="{{ old('guardian_social_media.whatsapp') }}">
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- القسم الرابع: بيانات الأم (ديناميكي) -->
            <div id="mother_section" class="bg-gradient-to-r from-pink-600 to-rose-600 px-8 py-6">
                <div class="flex items-center">
                    <div class="bg-white bg-opacity-20 rounded-full p-3 ml-4">
                        <i class="fas fa-female text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">بيانات الأم</h2>
                        <p class="text-pink-100 text-sm">معلومات الأم (تظهر فقط إذا لم تكن الأم هي ولي الأمر)</p>
                    </div>
                </div>
            </div>

            <div id="mother_fields" class="p-8">
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-400"></i>
                        </div>
                        <div class="mr-3">
                            <p class="text-sm text-blue-700">
                                <strong>ملاحظة:</strong> هذا القسم اختياري ويظهر فقط إذا لم تكن الأم هي ولي الأمر المسجل أعلاه.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- الاسم الكامل للأم -->
                    <div class="md:col-span-2">
                        <label for="mother_full_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user text-pink-600 ml-2"></i>
                            الاسم الكامل للأم
                        </label>
                        <input type="text" 
                               id="mother_full_name" 
                               name="mother_full_name" 
                               placeholder="مثال: فاطمة أحمد محمد علي"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition-all duration-200 text-right"
                               value="{{ old('mother_full_name') }}">
                        @error('mother_full_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- الرقم القومي للأم -->
                    <div>
                        <label for="mother_national_id" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-id-badge text-pink-600 ml-2"></i>
                            الرقم القومي للأم (14 رقم)
                        </label>
                        <input type="text" 
                               id="mother_national_id" 
                               name="mother_national_id" 
                               maxlength="14"
                               pattern="[0-9]{14}"
                               placeholder="12345678901234"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition-all duration-200"
                               value="{{ old('mother_national_id') }}">
                        @error('mother_national_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- الوظيفة -->
                    <div>
                        <label for="mother_job_title" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-briefcase text-pink-600 ml-2"></i>
                            الوظيفة
                        </label>
                        <input type="text" 
                               id="mother_job_title" 
                               name="mother_job_title" 
                               placeholder="مثال: مدرسة"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition-all duration-200 text-right"
                               value="{{ old('mother_job_title') }}">
                    </div>

                    <!-- جهة العمل -->
                    <div>
                        <label for="mother_workplace" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-building text-pink-600 ml-2"></i>
                            جهة العمل
                        </label>
                        <input type="text" 
                               id="mother_workplace" 
                               name="mother_workplace" 
                               placeholder="مثال: مدرسة النور الابتدائية"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition-all duration-200 text-right"
                               value="{{ old('mother_workplace') }}">
                    </div>

                    <!-- رقم الهاتف المحمول -->
                    <div>
                        <label for="mother_mobile_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-mobile-alt text-pink-600 ml-2"></i>
                            رقم الهاتف المحمول
                        </label>
                        <input type="tel" 
                               id="mother_mobile_phone" 
                               name="mother_mobile_phone" 
                               placeholder="01012345678"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition-all duration-200"
                               value="{{ old('mother_mobile_phone') }}">
                        @error('mother_mobile_phone')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- البريد الإلكتروني -->
                    <div>
                        <label for="mother_email" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-envelope text-pink-600 ml-2"></i>
                            البريد الإلكتروني
                        </label>
                        <input type="email" 
                               id="mother_email" 
                               name="mother_email" 
                               placeholder="example@email.com"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition-all duration-200"
                               value="{{ old('mother_email') }}">
                        @error('mother_email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- المؤهل الدراسي -->
                    <div>
                        <label for="mother_education_level" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-graduation-cap text-pink-600 ml-2"></i>
                            المؤهل الدراسي
                        </label>
                        <select id="mother_education_level" 
                                name="mother_education_level" 
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition-all duration-200">
                            <option value="">اختر المؤهل الدراسي</option>
                            <option value="ابتدائية" {{ old('mother_education_level') == 'ابتدائية' ? 'selected' : '' }}>ابتدائية</option>
                            <option value="إعدادية" {{ old('mother_education_level') == 'إعدادية' ? 'selected' : '' }}>إعدادية</option>
                            <option value="ثانوية عامة" {{ old('mother_education_level') == 'ثانوية عامة' ? 'selected' : '' }}>ثانوية عامة</option>
                            <option value="ثانوية فنية" {{ old('mother_education_level') == 'ثانوية فنية' ? 'selected' : '' }}>ثانوية فنية</option>
                            <option value="دبلوم" {{ old('mother_education_level') == 'دبلوم' ? 'selected' : '' }}>دبلوم</option>
                            <option value="بكالوريوس" {{ old('mother_education_level') == 'بكالوريوس' ? 'selected' : '' }}>بكالوريوس</option>
                            <option value="ماجستير" {{ old('mother_education_level') == 'ماجستير' ? 'selected' : '' }}>ماجستير</option>
                            <option value="دكتوراه" {{ old('mother_education_level') == 'دكتوراه' ? 'selected' : '' }}>دكتوراه</option>
                        </select>
                    </div>

                    <!-- العنوان الحالي -->
                    <div class="md:col-span-2">
                        <label for="mother_address" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-home text-pink-600 ml-2"></i>
                            العنوان الحالي
                        </label>
                        <textarea id="mother_address" 
                                  name="mother_address" 
                                  rows="3"
                                  placeholder="العنوان التفصيلي للأم"
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition-all duration-200 text-right resize-none">{{ old('mother_address') }}</textarea>
                        @error('mother_address')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- صلة الأم بالطالب -->
                    <div class="md:col-span-2">
                        <label for="mother_relationship" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-heart text-pink-600 ml-2"></i>
                            صلة الأم بالطالب
                        </label>
                        <select id="mother_relationship" 
                                name="mother_relationship" 
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-pink-500 focus:ring-2 focus:ring-pink-200 transition-all duration-200">
                            <option value="">اختر صلة القرابة</option>
                            <option value="أم" {{ old('mother_relationship') == 'أم' ? 'selected' : '' }}>أم</option>
                            <option value="زوجة الأب" {{ old('mother_relationship') == 'زوجة الأب' ? 'selected' : '' }}>زوجة الأب</option>
                            <option value="أخرى" {{ old('mother_relationship') == 'أخرى' ? 'selected' : '' }}>أخرى</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- القسم الخامس: بيانات الطوارئ -->
            <div class="bg-gradient-to-r from-red-600 to-orange-600 px-8 py-6">
                <div class="flex items-center">
                    <div class="bg-white bg-opacity-20 rounded-full p-3 ml-4">
                        <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">بيانات الطوارئ</h2>
                        <p class="text-red-100 text-sm">جهة الاتصال في حالات الطوارئ</p>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="mr-3">
                            <p class="text-sm text-red-700">
                                <strong>مهم:</strong> يرجى تسجيل بيانات شخص يمكن الاتصال به في حالات الطوارئ (غير ولي الأمر المسجل).
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- اسم جهة الاتصال في حالة الطوارئ -->
                    <div class="md:col-span-2">
                        <label for="emergency_contact_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user-shield text-red-600 ml-2"></i>
                            اسم جهة الاتصال في حالة الطوارئ
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="emergency_contact_name" 
                               name="emergency_contact_name" 
                               required
                               placeholder="مثال: أحمد محمد علي"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all duration-200 text-right"
                               value="{{ old('emergency_contact_name') }}">
                        @error('emergency_contact_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- صلة القرابة -->
                    <div>
                        <label for="emergency_relationship" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-users text-red-600 ml-2"></i>
                            صلة القرابة
                            <span class="text-red-500">*</span>
                        </label>
                        <select id="emergency_relationship" 
                                name="emergency_relationship" 
                                required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all duration-200">
                            <option value="">اختر صلة القرابة</option>
                            <option value="العم" {{ old('emergency_relationship') == 'العم' ? 'selected' : '' }}>العم</option>
                            <option value="العمة" {{ old('emergency_relationship') == 'العمة' ? 'selected' : '' }}>العمة</option>
                            <option value="الخال" {{ old('emergency_relationship') == 'الخال' ? 'selected' : '' }}>الخال</option>
                            <option value="الخالة" {{ old('emergency_relationship') == 'الخالة' ? 'selected' : '' }}>الخالة</option>
                            <option value="الجد" {{ old('emergency_relationship') == 'الجد' ? 'selected' : '' }}>الجد</option>
                            <option value="الجدة" {{ old('emergency_relationship') == 'الجدة' ? 'selected' : '' }}>الجدة</option>
                            <option value="صديق العائلة" {{ old('emergency_relationship') == 'صديق العائلة' ? 'selected' : '' }}>صديق العائلة</option>
                            <option value="جار" {{ old('emergency_relationship') == 'جار' ? 'selected' : '' }}>جار</option>
                            <option value="أخرى" {{ old('emergency_relationship') == 'أخرى' ? 'selected' : '' }}>أخرى</option>
                        </select>
                        @error('emergency_relationship')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- رقم الهاتف -->
                    <div>
                        <label for="emergency_phone" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-phone text-red-600 ml-2"></i>
                            رقم الهاتف
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" 
                               id="emergency_phone" 
                               name="emergency_phone" 
                               required
                               placeholder="01012345678"
                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all duration-200"
                               value="{{ old('emergency_phone') }}">
                        @error('emergency_phone')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- العنوان (اختياري) -->
                    <div class="md:col-span-2">
                        <label for="emergency_address" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt text-red-600 ml-2"></i>
                            العنوان (اختياري)
                        </label>
                        <textarea id="emergency_address" 
                                  name="emergency_address" 
                                  rows="3"
                                  placeholder="العنوان التفصيلي لجهة الاتصال في الطوارئ"
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-red-500 focus:ring-2 focus:ring-red-200 transition-all duration-200 text-right resize-none">{{ old('emergency_address') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- القسم السادس: البيانات المالية -->
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-8 py-6">
                <div class="flex items-center">
                    <div class="bg-white bg-opacity-20 rounded-full p-3 ml-4">
                        <i class="fas fa-money-bill-wave text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">البيانات المالية</h2>
                        <p class="text-green-100 text-sm">إعداد المصروفات والأقساط المدرسية</p>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-calculator text-green-400"></i>
                        </div>
                        <div class="mr-3">
                            <p class="text-sm text-green-700">
                                <strong>ملاحظة:</strong> سيتم حساب المصروفات تلقائياً بناءً على المرحلة الدراسية والخطة المختارة.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- القسم الأيسر: إعدادات المصروفات -->
                    <div class="lg:col-span-2 space-y-6">
                        
                        <!-- خطة المصروفات -->
                        <div class="bg-white border-2 border-gray-100 rounded-xl p-6 shadow-sm financial-card">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-clipboard-list text-green-600 ml-2"></i>
                                خطة المصروفات
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- نوع خطة المصروفات -->
                                <div class="md:col-span-2">
                                    <label for="fee_plan_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-list-alt text-green-600 ml-2"></i>
                                        خطة المصروفات
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select id="fee_plan_id" 
                                            name="fee_plan_id" 
                                            required
                                            onchange="calculateFees()"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200">
                                        <option value="">اختر خطة المصروفات</option>
                                        <option value="standard" data-amount="5000" data-description="الخطة الأساسية">الخطة الأساسية - 5,000 جنيه</option>
                                        <option value="premium" data-amount="7500" data-description="الخطة المتقدمة">الخطة المتقدمة - 7,500 جنيه</option>
                                        <option value="vip" data-amount="10000" data-description="الخطة المميزة">الخطة المميزة - 10,000 جنيه</option>
                                    </select>
                                    @error('fee_plan_id')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- الرسوم الإضافية -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-3">
                                        <i class="fas fa-plus-circle text-green-600 ml-2"></i>
                                        الرسوم الإضافية
                                    </label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   name="additional_fees[]" 
                                                   value="transport" 
                                                   data-amount="500"
                                                   onchange="calculateFees()"
                                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                            <span class="mr-2 text-sm">رسوم النقل - 500 جنيه</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   name="additional_fees[]" 
                                                   value="meals" 
                                                   data-amount="300"
                                                   onchange="calculateFees()"
                                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                            <span class="mr-2 text-sm">رسوم الوجبات - 300 جنيه</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   name="additional_fees[]" 
                                                   value="activities" 
                                                   data-amount="200"
                                                   onchange="calculateFees()"
                                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                            <span class="mr-2 text-sm">رسوم الأنشطة - 200 جنيه</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="checkbox" 
                                                   name="additional_fees[]" 
                                                   value="books" 
                                                   data-amount="400"
                                                   onchange="calculateFees()"
                                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                            <span class="mr-2 text-sm">رسوم الكتب - 400 جنيه</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- الخصومات والمنح -->
                                <div>
                                    <label for="discount_type" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-percentage text-green-600 ml-2"></i>
                                        الخصومات والمنح
                                    </label>
                                    <select id="discount_type" 
                                            name="discount_type" 
                                            onchange="calculateFees()"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200 mb-3">
                                        <option value="">بدون خصم</option>
                                        <option value="sibling" data-discount="10">خصم الأشقاء - 10%</option>
                                        <option value="excellence" data-discount="15">خصم التفوق - 15%</option>
                                        <option value="financial" data-discount="25">منحة مالية - 25%</option>
                                        <option value="staff" data-discount="50">خصم الموظفين - 50%</option>
                                        <option value="custom" data-discount="0">خصم مخصص</option>
                                    </select>

                                    <!-- خصم مخصص -->
                                    <div id="custom_discount_section" style="display: none;">
                                        <label for="custom_discount" class="block text-xs text-gray-600 mb-1">نسبة الخصم المخصص (%)</label>
                                        <input type="number" 
                                               id="custom_discount" 
                                               name="custom_discount" 
                                               min="0" 
                                               max="100" 
                                               step="0.1"
                                               onchange="calculateFees()"
                                               placeholder="0.0"
                                               class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:border-green-500 focus:ring-1 focus:ring-green-200 transition-all duration-200">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- إعدادات الأقساط -->
                        <div class="bg-white border-2 border-gray-100 rounded-xl p-6 shadow-sm financial-card">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-calendar-alt text-green-600 ml-2"></i>
                                إعدادات الأقساط
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- نظام الدفع -->
                                <div>
                                    <label for="payment_system" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-credit-card text-green-600 ml-2"></i>
                                        نظام الدفع
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <select id="payment_system" 
                                            name="payment_system" 
                                            required
                                            onchange="updateInstallmentOptions()"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200">
                                        <option value="">اختر نظام الدفع</option>
                                        <option value="full_payment">دفع كامل</option>
                                        <option value="installments">دفع بالأقساط</option>
                                    </select>
                                </div>

                                <!-- عدد الأقساط -->
                                <div id="installments_section" style="display: none;">
                                    <label for="installments_count" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-list-ol text-green-600 ml-2"></i>
                                        عدد الأقساط
                                    </label>
                                    <select id="installments_count" 
                                            name="installments_count" 
                                            onchange="calculateInstallments()"
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200">
                                        <option value="2">قسطين</option>
                                        <option value="3">3 أقساط</option>
                                        <option value="4">4 أقساط</option>
                                        <option value="6">6 أقساط</option>
                                        <option value="10">10 أقساط</option>
                                    </select>
                                </div>

                                <!-- تاريخ بداية الأقساط -->
                                <div id="start_date_section" style="display: none;">
                                    <label for="installments_start_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-calendar-day text-green-600 ml-2"></i>
                                        تاريخ بداية الأقساط
                                    </label>
                                    <input type="date" 
                                           id="installments_start_date" 
                                           name="installments_start_date" 
                                           onchange="calculateInstallments()"
                                           class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200">
                                </div>

                                <!-- طريقة الدفع المفضلة -->
                                <div>
                                    <label for="preferred_payment_method" class="block text-sm font-semibold text-gray-700 mb-2">
                                        <i class="fas fa-wallet text-green-600 ml-2"></i>
                                        طريقة الدفع المفضلة
                                    </label>
                                    <select id="preferred_payment_method" 
                                            name="preferred_payment_method" 
                                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200">
                                        <option value="cash">نقدي</option>
                                        <option value="bank_transfer">تحويل بنكي</option>
                                        <option value="credit_card">بطاقة ائتمان</option>
                                        <option value="debit_card">بطاقة خصم</option>
                                        <option value="mobile_wallet">محفظة إلكترونية</option>
                                        <option value="fawry">فوري</option>
                                        <option value="vodafone_cash">فودافون كاش</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- ملاحظات مالية -->
                        <div class="bg-white border-2 border-gray-100 rounded-xl p-6 shadow-sm financial-card">
                            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-sticky-note text-green-600 ml-2"></i>
                                ملاحظات مالية
                            </h3>
                            
                            <div class="space-y-4">
                                <!-- ملاحظات خاصة -->
                                <div>
                                    <label for="financial_notes" class="block text-sm font-semibold text-gray-700 mb-2">
                                        ملاحظات خاصة بالمصروفات
                                    </label>
                                    <textarea id="financial_notes" 
                                              name="financial_notes" 
                                              rows="3"
                                              placeholder="أي ملاحظات خاصة بالوضع المالي للطالب..."
                                              class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all duration-200 text-right resize-none">{{ old('financial_notes') }}</textarea>
                                </div>

                                <!-- حالة خاصة -->
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               name="special_financial_case" 
                                               value="1"
                                               class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                                        <span class="mr-2 text-sm font-medium text-gray-700">حالة مالية خاصة تتطلب مراجعة الإدارة</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- القسم الأيمن: ملخص المصروفات -->
                    <div class="lg:col-span-1">
                        <div class="financial-summary rounded-xl p-6 sticky top-4">
                            <h3 class="text-lg font-bold text-green-800 mb-4 flex items-center">
                                <i class="fas fa-receipt text-green-600 ml-2"></i>
                                ملخص المصروفات
                            </h3>
                            
                            <div class="space-y-4">
                                <!-- المصروفات الأساسية -->
                                <div class="flex justify-between items-center py-2 border-b border-green-200">
                                    <span class="text-sm text-gray-600">المصروفات الأساسية:</span>
                                    <span id="basic_fees" class="font-semibold text-green-800">0 جنيه</span>
                                </div>

                                <!-- الرسوم الإضافية -->
                                <div class="flex justify-between items-center py-2 border-b border-green-200">
                                    <span class="text-sm text-gray-600">الرسوم الإضافية:</span>
                                    <span id="additional_fees_total" class="font-semibold text-green-800">0 جنيه</span>
                                </div>

                                <!-- المجموع الفرعي -->
                                <div class="flex justify-between items-center py-2 border-b border-green-200">
                                    <span class="text-sm text-gray-600">المجموع الفرعي:</span>
                                    <span id="subtotal" class="font-semibold text-green-800">0 جنيه</span>
                                </div>

                                <!-- الخصم -->
                                <div class="flex justify-between items-center py-2 border-b border-green-200">
                                    <span class="text-sm text-gray-600">الخصم:</span>
                                    <span id="discount_amount" class="font-semibold text-red-600">- 0 جنيه</span>
                                </div>

                                <!-- الإجمالي النهائي -->
                                <div class="flex justify-between items-center py-3 bg-green-100 rounded-lg px-3 border-2 border-green-300">
                                    <span class="font-bold text-green-800">الإجمالي النهائي:</span>
                                    <span id="final_total" class="font-bold text-xl text-green-800">0 جنيه</span>
                                </div>

                                <!-- تفاصيل الأقساط -->
                                <div id="installments_details" style="display: none;" class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                                    <h4 class="font-semibold text-blue-800 mb-3 flex items-center">
                                        <i class="fas fa-calendar-check text-blue-600 ml-2"></i>
                                        تفاصيل الأقساط
                                    </h4>
                                    <div id="installments_breakdown" class="space-y-2 text-sm">
                                        <!-- سيتم ملؤها بـ JavaScript -->
                                    </div>
                                </div>

                                <!-- معلومات إضافية -->
                                <div class="mt-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <h4 class="font-semibold text-yellow-800 mb-2 flex items-center">
                                        <i class="fas fa-info-circle text-yellow-600 ml-2"></i>
                                        معلومات مهمة
                                    </h4>
                                    <ul class="text-xs text-yellow-700 space-y-1">
                                        <li>• يمكن تعديل المصروفات لاحقاً من خلال الإدارة المالية</li>
                                        <li>• الخصومات تحتاج موافقة الإدارة</li>
                                        <li>• تواريخ الأقساط قابلة للتعديل</li>
                                        <li>• رسوم التأخير 2% شهرياً</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row justify-between items-center mt-8 pt-6 border-t border-gray-200">
                    <div class="flex space-x-4 space-x-reverse w-full sm:w-auto">
                        <button type="submit" 
                                class="flex-1 sm:flex-none bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-8 py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                            <i class="fas fa-save ml-2"></i>
                            حفظ البيانات
                        </button>
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex-1 sm:flex-none bg-gray-500 text-white px-8 py-3 rounded-lg font-semibold hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 text-center">
                            <i class="fas fa-times ml-2"></i>
                            إلغاء
                        </a>
                    </div>
                    
                    <div class="text-sm text-gray-500 mt-4 sm:mt-0">
                        <i class="fas fa-info-circle text-blue-500"></i>
                        الحقول المميزة بـ <span class="text-red-500">*</span> مطلوبة
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* تحسينات إضافية للفورم */
.form-input:focus {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* تأثيرات الحركة */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.bg-white {
    animation: slideInUp 0.6s ease-out;
}

/* تحسين المظهر للموبايل */
@media (max-width: 640px) {
    .grid {
        grid-template-columns: 1fr;
    }
    
    .md\:col-span-2 {
        grid-column: span 1;
    }
}

/* تحسينات القسم المالي */
.financial-card {
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.financial-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    border-color: #10b981;
}

.financial-summary {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border: 2px solid #bbf7d0;
    position: sticky;
    top: 20px;
}

.installment-item {
    transition: all 0.2s ease;
    border-radius: 8px;
    padding: 8px;
}

.installment-item:hover {
    background-color: #eff6ff;
    transform: translateX(5px);
}

/* تأثيرات الخصومات */
.discount-highlight {
    background: linear-gradient(90deg, #fef3c7, #fde68a);
    border: 1px solid #f59e0b;
    border-radius: 6px;
    padding: 4px 8px;
    animation: pulse 2s infinite;
}

/* تأثيرات الأرقام المتحركة */
.animated-number {
    transition: all 0.5s ease;
    font-weight: bold;
}

.number-increase {
    color: #059669;
    transform: scale(1.1);
}

.number-decrease {
    color: #dc2626;
    transform: scale(0.9);
}

/* تحسينات الـ checkboxes */
input[type="checkbox"]:checked + span {
    background-color: #f0fdf4;
    border-radius: 6px;
    padding: 4px 8px;
    border: 1px solid #bbf7d0;
    transition: all 0.3s ease;
}

/* تأثيرات التحميل */
.loading-shimmer {
    background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%);
    background-size: 200% 100%;
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% {
        background-position: -200% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

/* تحسينات الأزرار */
.btn-financial {
    background: linear-gradient(135deg, #10b981, #059669);
    border: none;
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.btn-financial:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
    background: linear-gradient(135deg, #059669, #047857);
}

/* تحسينات الإشعارات */
.notification-success {
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    border: 1px solid #10b981;
    border-radius: 8px;
    padding: 12px;
    animation: slideInRight 0.5s ease;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* تحسينات الجداول */
.financial-table {
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.financial-table th {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
    font-weight: 600;
    padding: 16px;
    text-align: right;
}

.financial-table td {
    padding: 12px 16px;
    border-bottom: 1px solid #e5e7eb;
    transition: background-color 0.2s ease;
}

.financial-table tr:hover td {
    background-color: #f9fafb;
}

/* تحسينات الأيقونات */
.icon-bounce {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

/* تحسينات الـ tooltips */
.tooltip {
    position: relative;
    display: inline-block;
}

.tooltip .tooltiptext {
    visibility: hidden;
    width: 200px;
    background-color: #374151;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 8px;
    position: absolute;
    z-index: 1;
    bottom: 125%;
    left: 50%;
    margin-left: -100px;
    opacity: 0;
    transition: opacity 0.3s;
    font-size: 12px;
}

.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}

/* تحسينات الـ progress bars */
.progress-bar {
    width: 100%;
    height: 8px;
    background-color: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #059669);
    border-radius: 4px;
    transition: width 0.5s ease;
}

/* تحسينات الـ modals */
.modal-overlay {
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.modal-content {
    background: white;
    border-radius: 16px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>

<script>
// تحقق من صحة الرقم القومي
document.getElementById('national_id').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    e.target.value = value;
    
    if (value.length === 14) {
        e.target.classList.remove('border-red-300');
        e.target.classList.add('border-green-300');
    } else {
        e.target.classList.remove('border-green-300');
        e.target.classList.add('border-red-300');
    }
});

// تأثيرات بصرية للحقول
document.querySelectorAll('input, select, textarea').forEach(element => {
    element.addEventListener('focus', function() {
        this.parentElement.classList.add('transform', 'scale-105');
    });
    
    element.addEventListener('blur', function() {
        this.parentElement.classList.remove('transform', 'scale-105');
    });
});

// دالة لإظهار الإشعارات
function showNotification(message, type = 'info') {
    // إنشاء عنصر الإشعار
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 max-w-sm w-full bg-white border-l-4 p-4 shadow-lg rounded-lg z-50 transition-all duration-300 transform translate-x-full`;
    
    // تحديد لون الحدود حسب النوع
    const borderColors = {
        'success': 'border-green-500',
        'error': 'border-red-500',
        'warning': 'border-yellow-500',
        'info': 'border-blue-500'
    };
    
    const iconColors = {
        'success': 'text-green-500',
        'error': 'text-red-500',
        'warning': 'text-yellow-500',
        'info': 'text-blue-500'
    };
    
    const icons = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-exclamation-circle',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    };
    
    notification.classList.add(borderColors[type] || borderColors.info);
    
    notification.innerHTML = `
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="${icons[type] || icons.info} ${iconColors[type] || iconColors.info}"></i>
            </div>
            <div class="mr-3 flex-1">
                <p class="text-sm text-gray-700">${message}</p>
            </div>
            <div class="flex-shrink-0">
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    `;
    
    // إضافة الإشعار للصفحة
    document.body.appendChild(notification);
    
    // تأثير الظهور
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // إزالة الإشعار تلقائياً بعد 5 ثوانٍ
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}
</script>
@endsection

@section('scripts')
<script>
// ===== النظام الجديد يعتمد على APIs - لا حاجة للبيانات الثابتة =====
// تم استبدال البيانات الثابتة بـ APIs ديناميكية تقرأ من قاعدة البيانات

// تحديث الصفوف عند اختيار المرحلة (API-driven)
async function updateGrades() {
    const gradeLevelSelect = document.getElementById('grade_level');
    const gradeSelect = document.getElementById('grade');
    const classroomSelect = document.getElementById('classroom');

    const selectedLevel = gradeLevelSelect.value;

    // إعادة تعيين الصفوف والفصول
    gradeSelect.innerHTML = '<option value="">اختر الصف الدراسي</option>';
    classroomSelect.innerHTML = '<option value="">اختر الفصل</option>';
    classroomSelect.disabled = true;

    if (!selectedLevel) {
        gradeSelect.disabled = true;
        return;
    }

    // إظهار loading state
    gradeSelect.innerHTML = '<option value="">جاري التحميل...</option>';
    gradeSelect.disabled = true;

    try {
        // استدعاء API للحصول على الصفوف
        const response = await fetch(`{{ url('/api/dropdown/grades') }}?level=${selectedLevel}`);
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            // إعادة تعيين وتفعيل dropdown
            gradeSelect.innerHTML = '<option value="">اختر الصف الدراسي</option>';
            gradeSelect.disabled = false;

            // إضافة الصفوف
            data.data.forEach(grade => {
                const option = document.createElement('option');
                option.value = grade.value; // grade_1, prep_1, etc.
                option.textContent = grade.label;
                option.dataset.gradeId = grade.id;
                option.dataset.classroomsCount = grade.classrooms_count;
                gradeSelect.appendChild(option);
            });

            console.log(`تم تحميل ${data.data.length} صف للمرحلة ${selectedLevel}`);
        } else {
            gradeSelect.innerHTML = '<option value="">لا توجد صفوف متاحة</option>';
            gradeSelect.disabled = true;
        }
    } catch (error) {
        console.error('خطأ في تحميل الصفوف:', error);
        gradeSelect.innerHTML = '<option value="">حدث خطأ في التحميل</option>';
        gradeSelect.disabled = true;
        
        // إظهار رسالة خطأ للمستخدم
        showNotification('حدث خطأ أثناء تحميل الصفوف الدراسية', 'error');
    }
}

// تحديث الفصول عند اختيار الصف (API-driven)
async function updateClassrooms() {
    const gradeSelect = document.getElementById('grade');
    const classroomSelect = document.getElementById('classroom');

    const selectedGrade = gradeSelect.value;

    classroomSelect.innerHTML = '<option value="">اختر الفصل</option>';

    if (!selectedGrade) {
        classroomSelect.disabled = true;
        return;
    }

    // إظهار loading state
    classroomSelect.innerHTML = '<option value="">جاري التحميل...</option>';
    classroomSelect.disabled = true;

    try {
        // استدعاء API للحصول على الفصول
        const response = await fetch(`{{ url('/api/dropdown/classrooms') }}?grade=${selectedGrade}`);
        const data = await response.json();

        if (data.success && data.data.length > 0) {
            // إعادة تعيين وتفعيل dropdown
            classroomSelect.innerHTML = '<option value="">اختر الفصل</option>';
            classroomSelect.disabled = false;

            // إضافة الفصول
            data.data.forEach(classroom => {
                const option = document.createElement('option');
                option.value = classroom.value; // 1A, 1B, 1A PRE, etc.
                option.textContent = classroom.label; // فصل 1A
                option.dataset.classroomId = classroom.id;
                option.dataset.capacity = classroom.capacity;
                option.dataset.currentStudents = classroom.current_students;
                option.dataset.availableSeats = classroom.available_seats;
                option.dataset.isFull = classroom.is_full;
                
                // إضافة معلومات السعة إذا كان الفصل ممتلئاً
                if (classroom.is_full) {
                    option.textContent += ' (ممتلئ)';
                    option.style.color = '#dc2626';
                } else if (classroom.available_seats <= 5) {
                    option.textContent += ` (${classroom.available_seats} مقاعد متبقية)`;
                    option.style.color = '#f59e0b';
                }
                
                classroomSelect.appendChild(option);
            });

            console.log(`تم تحميل ${data.data.length} فصل للصف ${data.grade_info.name}`);
            
            // إظهار معلومات إضافية في console
            console.log('معلومات الفصول:', data.data);
        } else {
            classroomSelect.innerHTML = '<option value="">لا توجد فصول متاحة</option>';
            classroomSelect.disabled = true;
        }
    } catch (error) {
        console.error('خطأ في تحميل الفصول:', error);
        classroomSelect.innerHTML = '<option value="">حدث خطأ في التحميل</option>';
        classroomSelect.disabled = true;
        
        // إظهار رسالة خطأ للمستخدم
        showNotification('حدث خطأ أثناء تحميل الفصول الدراسية', 'error');
    }
}

// إظهار/إخفاء حقول المدرسة السابقة
function togglePreviousSchoolFields() {
    const enrollmentType = document.getElementById('enrollment_type').value;
    const previousSchoolField = document.getElementById('previous_school_field');
    const transferReasonField = document.getElementById('transfer_reason_field');
    const previousSchoolInput = document.getElementById('previous_school');
    const transferReasonInput = document.getElementById('transfer_reason');

    if (enrollmentType === 'transfer') {
        // إظهار حقول التحويل
        previousSchoolField.classList.remove('hidden');
        transferReasonField.classList.remove('hidden');
        previousSchoolInput.required = true;
        transferReasonInput.required = true;
    } else if (enrollmentType === 'return') {
        // إظهار حقل المدرسة السابقة فقط
        previousSchoolField.classList.remove('hidden');
        transferReasonField.classList.add('hidden');
        previousSchoolInput.required = true;
        transferReasonInput.required = false;
    } else {
        // إخفاء جميع الحقول
        previousSchoolField.classList.add('hidden');
        transferReasonField.classList.add('hidden');
        previousSchoolInput.required = false;
        transferReasonInput.required = false;
    }
}

// وظيفة إظهار/إخفاء حقول الوصي القانوني
function toggleLegalGuardianFields() {
    const hasLegalGuardianYes = document.getElementById('has_legal_guardian_yes');
    const legalGuardianFields = document.getElementById('legal_guardian_fields');
    
    if (hasLegalGuardianYes && hasLegalGuardianYes.checked) {
        legalGuardianFields.classList.remove('hidden');
        // جعل الحقول مطلوبة
        document.getElementById('legal_guardian_full_name').required = true;
        document.getElementById('legal_guardian_national_id').required = true;
        document.getElementById('legal_guardian_relationship').required = true;
        document.getElementById('legal_guardian_phone').required = true;
        document.getElementById('legal_guardian_address').required = true;
    } else {
        legalGuardianFields.classList.add('hidden');
        // إزالة الحقول المطلوبة
        document.getElementById('legal_guardian_full_name').required = false;
        document.getElementById('legal_guardian_national_id').required = false;
        document.getElementById('legal_guardian_relationship').required = false;
        document.getElementById('legal_guardian_phone').required = false;
        document.getElementById('legal_guardian_address').required = false;
        
        // مسح القيم
        document.getElementById('legal_guardian_full_name').value = '';
        document.getElementById('legal_guardian_national_id').value = '';
        document.getElementById('legal_guardian_relationship').value = '';
        document.getElementById('legal_guardian_phone').value = '';
        document.getElementById('legal_guardian_address').value = '';
        document.getElementById('legal_guardian_document_number').value = '';
        document.getElementById('legal_guardian_document_details').value = '';
    }
}

// وظيفة إظهار/إخفاء قسم بيانات الأم
function toggleMotherSection() {
    const guardianRelationship = document.getElementById('guardian_relationship').value;
    const motherSection = document.getElementById('mother_section');
    const motherFields = document.getElementById('mother_fields');
    
    if (guardianRelationship === 'الأم') {
        // إخفاء قسم الأم إذا كانت الأم هي ولي الأمر
        motherSection.style.display = 'none';
        motherFields.style.display = 'none';
        
        // إزالة required من حقول الأم
        document.getElementById('mother_full_name').required = false;
        document.getElementById('mother_national_id').required = false;
        document.getElementById('mother_mobile_phone').required = false;
        
        // مسح قيم حقول الأم
        document.getElementById('mother_full_name').value = '';
        document.getElementById('mother_national_id').value = '';
        document.getElementById('mother_job_title').value = '';
        document.getElementById('mother_workplace').value = '';
        document.getElementById('mother_mobile_phone').value = '';
        document.getElementById('mother_email').value = '';
        document.getElementById('mother_education_level').value = '';
        document.getElementById('mother_address').value = '';
        document.getElementById('mother_relationship').value = '';
        
        console.log('Mother section hidden - Guardian is mother');
    } else {
        // إظهار قسم الأم إذا لم تكن الأم هي ولي الأمر
        motherSection.style.display = 'block';
        motherFields.style.display = 'block';
        
        console.log('Mother section shown - Guardian is not mother');
    }
}

// ربط الوظيفة بتغيير صلة القرابة
document.getElementById('guardian_relationship').addEventListener('change', toggleMotherSection);

// تشغيل الوظيفة عند تحميل الصفحة للحفاظ على الحالة
document.addEventListener('DOMContentLoaded', function() {
    toggleLegalGuardianFields();
    toggleMotherSection();
});

// ==================== وظائف النظام المالي ====================

// متغيرات النظام المالي
let currentBasicFees = 0;
let currentAdditionalFees = 0;
let currentDiscount = 0;
let currentDiscountAmount = 0;
let finalTotal = 0;

// حساب المصروفات الإجمالية
function calculateFees() {
    // الحصول على المصروفات الأساسية
    const feePlanSelect = document.getElementById('fee_plan_id');
    const selectedOption = feePlanSelect.options[feePlanSelect.selectedIndex];
    currentBasicFees = selectedOption.dataset.amount ? parseFloat(selectedOption.dataset.amount) : 0;
    
    // حساب الرسوم الإضافية
    currentAdditionalFees = 0;
    const additionalFeesCheckboxes = document.querySelectorAll('input[name="additional_fees[]"]:checked');
    additionalFeesCheckboxes.forEach(checkbox => {
        currentAdditionalFees += parseFloat(checkbox.dataset.amount || 0);
    });
    
    // حساب المجموع الفرعي
    const subtotal = currentBasicFees + currentAdditionalFees;
    
    // حساب الخصم
    calculateDiscount(subtotal);
    
    // حساب الإجمالي النهائي
    finalTotal = subtotal - currentDiscountAmount;
    
    // تحديث العرض
    updateFinancialSummary();
    
    // إعادة حساب الأقساط إذا كان نظام الأقساط مفعل
    if (document.getElementById('payment_system').value === 'installments') {
        calculateInstallments();
    }
}

// حساب الخصم
function calculateDiscount(subtotal) {
    const discountSelect = document.getElementById('discount_type');
    const selectedDiscount = discountSelect.options[discountSelect.selectedIndex];
    
    if (selectedDiscount.value === 'custom') {
        // خصم مخصص
        const customDiscountInput = document.getElementById('custom_discount');
        currentDiscount = parseFloat(customDiscountInput.value || 0);
        document.getElementById('custom_discount_section').style.display = 'block';
    } else if (selectedDiscount.dataset.discount) {
        // خصم محدد مسبقاً
        currentDiscount = parseFloat(selectedDiscount.dataset.discount);
        document.getElementById('custom_discount_section').style.display = 'none';
    } else {
        // بدون خصم
        currentDiscount = 0;
        document.getElementById('custom_discount_section').style.display = 'none';
    }
    
    currentDiscountAmount = (subtotal * currentDiscount) / 100;
}

// تحديث ملخص المصروفات
function updateFinancialSummary() {
    document.getElementById('basic_fees').textContent = formatCurrency(currentBasicFees);
    document.getElementById('additional_fees_total').textContent = formatCurrency(currentAdditionalFees);
    document.getElementById('subtotal').textContent = formatCurrency(currentBasicFees + currentAdditionalFees);
    document.getElementById('discount_amount').textContent = '- ' + formatCurrency(currentDiscountAmount);
    document.getElementById('final_total').textContent = formatCurrency(finalTotal);
}

// تحديث خيارات الأقساط
function updateInstallmentOptions() {
    const paymentSystem = document.getElementById('payment_system').value;
    const installmentsSection = document.getElementById('installments_section');
    const startDateSection = document.getElementById('start_date_section');
    const installmentsDetails = document.getElementById('installments_details');
    
    if (paymentSystem === 'installments') {
        installmentsSection.style.display = 'block';
        startDateSection.style.display = 'block';
        installmentsDetails.style.display = 'block';
        
        // تعيين تاريخ افتراضي (بداية الشهر القادم)
        const nextMonth = new Date();
        nextMonth.setMonth(nextMonth.getMonth() + 1);
        nextMonth.setDate(1);
        document.getElementById('installments_start_date').value = nextMonth.toISOString().split('T')[0];
        
        calculateInstallments();
    } else {
        installmentsSection.style.display = 'none';
        startDateSection.style.display = 'none';
        installmentsDetails.style.display = 'none';
    }
}

// حساب تفاصيل الأقساط
function calculateInstallments() {
    const installmentsCount = parseInt(document.getElementById('installments_count').value || 2);
    const startDate = document.getElementById('installments_start_date').value;
    const installmentsBreakdown = document.getElementById('installments_breakdown');
    
    if (!startDate || finalTotal <= 0) {
        installmentsBreakdown.innerHTML = '<p class="text-gray-500 text-sm">يرجى تحديد المصروفات وتاريخ البداية</p>';
        return;
    }
    
    const installmentAmount = Math.round(finalTotal / installmentsCount);
    const lastInstallmentAmount = finalTotal - (installmentAmount * (installmentsCount - 1));
    
    let html = '';
    const startDateObj = new Date(startDate);
    
    for (let i = 0; i < installmentsCount; i++) {
        const installmentDate = new Date(startDateObj);
        installmentDate.setMonth(installmentDate.getMonth() + i);
        
        const amount = (i === installmentsCount - 1) ? lastInstallmentAmount : installmentAmount;
        const formattedDate = installmentDate.toLocaleDateString('ar-EG', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        html += `
            <div class="flex justify-between items-center py-1 border-b border-blue-100">
                <span class="text-blue-700">القسط ${i + 1}:</span>
                <div class="text-left">
                    <span class="font-semibold text-blue-800">${formatCurrency(amount)}</span>
                    <br>
                    <span class="text-xs text-blue-600">${formattedDate}</span>
                </div>
            </div>
        `;
    }
    
    installmentsBreakdown.innerHTML = html;
}

// تنسيق العملة
function formatCurrency(amount) {
    return new Intl.NumberFormat('ar-EG', {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount) + ' جنيه';
}

// تهيئة النظام المالي عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    // إضافة مستمعي الأحداث للحقول المالية
    const feePlanSelect = document.getElementById('fee_plan_id');
    const discountSelect = document.getElementById('discount_type');
    const paymentSystemSelect = document.getElementById('payment_system');
    const customDiscountInput = document.getElementById('custom_discount');
    
    if (feePlanSelect) feePlanSelect.addEventListener('change', calculateFees);
    if (discountSelect) discountSelect.addEventListener('change', calculateFees);
    if (paymentSystemSelect) paymentSystemSelect.addEventListener('change', updateInstallmentOptions);
    if (customDiscountInput) customDiscountInput.addEventListener('input', calculateFees);
    
    // إضافة مستمعي الأحداث للرسوم الإضافية
    const additionalFeesCheckboxes = document.querySelectorAll('input[name="additional_fees[]"]');
    additionalFeesCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', calculateFees);
    });
    
    // إضافة مستمعي الأحداث لحقول الأقساط
    const installmentsCountSelect = document.getElementById('installments_count');
    const startDateInput = document.getElementById('installments_start_date');
    
    if (installmentsCountSelect) installmentsCountSelect.addEventListener('change', calculateInstallments);
    if (startDateInput) startDateInput.addEventListener('change', calculateInstallments);
    
    // تحديث أولي للملخص المالي
    updateFinancialSummary();
});

// تأثيرات بصرية للقسم المالي
function addFinancialSectionEffects() {
    // تأثير hover للبطاقات المالية
    const financialCards = document.querySelectorAll('.bg-white.border-2.border-gray-100');
    financialCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.classList.add('shadow-lg', 'border-green-200');
            this.style.transform = 'translateY(-2px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('shadow-lg', 'border-green-200');
            this.style.transform = 'translateY(0)';
        });
    });
    
    // تأثير النقر على الخيارات
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const label = this.closest('label');
            if (this.checked) {
                label.classList.add('bg-green-50', 'border-green-200', 'rounded-lg', 'p-2');
            } else {
                label.classList.remove('bg-green-50', 'border-green-200', 'rounded-lg', 'p-2');
            }
        });
    });
}

// تشغيل التأثيرات عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', addFinancialSectionEffects);

// وظيفة التحقق من صحة البيانات المالية قبل الإرسال
function validateFinancialData() {
    const feePlanId = document.getElementById('fee_plan_id').value;
    const paymentSystem = document.getElementById('payment_system').value;
    
    if (!feePlanId) {
        alert('يرجى اختيار خطة المصروفات');
        document.getElementById('fee_plan_id').focus();
        return false;
    }
    
    if (!paymentSystem) {
        alert('يرجى اختيار نظام الدفع');
        document.getElementById('payment_system').focus();
        return false;
    }
    
    if (paymentSystem === 'installments') {
        const startDate = document.getElementById('installments_start_date').value;
        if (!startDate) {
            alert('يرجى تحديد تاريخ بداية الأقساط');
            document.getElementById('installments_start_date').focus();
            return false;
        }
    }
    
    return true;
}

// إضافة التحقق للفورم
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateFinancialData()) {
                e.preventDefault();
                return false;
            }
        });
    }
});

// وظائف مساعدة للتفاعل المتقدم
function animateValue(element, start, end, duration) {
    const startTime = performance.now();
    const startValue = start;
    const endValue = end;
    
    function updateValue(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const currentValue = startValue + (endValue - startValue) * progress;
        element.textContent = formatCurrency(Math.round(currentValue));
        
        if (progress < 1) {
            requestAnimationFrame(updateValue);
        }
    }
    
    requestAnimationFrame(updateValue);
}

// تحديث الملخص المالي مع الرسوم المتحركة
function updateFinancialSummaryAnimated() {
    const basicFeesElement = document.getElementById('basic_fees');
    const additionalFeesElement = document.getElementById('additional_fees_total');
    const subtotalElement = document.getElementById('subtotal');
    const discountElement = document.getElementById('discount_amount');
    const finalTotalElement = document.getElementById('final_total');
    
    // الحصول على القيم الحالية
    const currentBasicDisplay = parseFloat(basicFeesElement.textContent.replace(/[^\d]/g, '')) || 0;
    const currentAdditionalDisplay = parseFloat(additionalFeesElement.textContent.replace(/[^\d]/g, '')) || 0;
    const currentSubtotalDisplay = parseFloat(subtotalElement.textContent.replace(/[^\d]/g, '')) || 0;
    const currentDiscountDisplay = parseFloat(discountElement.textContent.replace(/[^\d]/g, '')) || 0;
    const currentFinalDisplay = parseFloat(finalTotalElement.textContent.replace(/[^\d]/g, '')) || 0;
    
    // تحريك القيم
    animateValue(basicFeesElement, currentBasicDisplay, currentBasicFees, 500);
    animateValue(additionalFeesElement, currentAdditionalDisplay, currentAdditionalFees, 500);
    animateValue(subtotalElement, currentSubtotalDisplay, currentBasicFees + currentAdditionalFees, 500);
    animateValue(finalTotalElement, currentFinalDisplay, finalTotal, 800);
    
    // تحديث الخصم مع علامة ناقص
    setTimeout(() => {
        discountElement.textContent = '- ' + formatCurrency(currentDiscountAmount);
        discountElement.classList.add('animate-pulse');
        setTimeout(() => discountElement.classList.remove('animate-pulse'), 1000);
    }, 300);
}

// استبدال updateFinancialSummary بالنسخة المتحركة
// (يمكن تفعيلها أو إلغاؤها حسب الحاجة)
// updateFinancialSummary = updateFinancialSummaryAnimated;

// ==================== Form Submission Debugging ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Form debugging script loaded');
    
    const form = document.getElementById('studentForm');
    if (!form) {
        console.error('❌ Student form not found!');
        return;
    }
    
    console.log('✅ Student form found');
    console.log('Form action:', form.action);
    console.log('Form method:', form.method);
    
    form.addEventListener('submit', function(e) {
        console.log('🚀 Form submission started');
        console.log('Current URL:', window.location.href);
        
        // طباعة بعض البيانات المهمة
        const formData = new FormData(form);
        const dataObj = {};
        for (let [key, value] of formData.entries()) {
            dataObj[key] = value;
        }
        console.log('📝 Form data preview:', {
            'full_name': dataObj.full_name,
            'national_id': dataObj.national_id,
            'grade_level': dataObj.grade_level,
            'grade': dataObj.grade,
            'classroom': dataObj.classroom
        });
        
        // التحقق من الحقول المطلوبة
        const requiredFields = form.querySelectorAll('[required]');
        const emptyFields = [];
        
        console.log('📋 Checking', requiredFields.length, 'required fields');
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                emptyFields.push(field.name || field.id);
            }
        });
        
        if (emptyFields.length > 0) {
            console.log('❌ Empty required fields:', emptyFields);
            alert('الحقول المطلوبة فارغة: ' + emptyFields.join(', '));
            e.preventDefault();
            return false;
        }
        
        console.log('✅ All required fields filled');
        console.log('📤 Form will be submitted now...');
        
        // إظهار loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            console.log('🔄 Changing submit button state');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i> جاري الحفظ...';
            submitBtn.disabled = true;
        }
        
        // إضافة معالج للـ form beforeunload
        window.addEventListener('beforeunload', function() {
            console.log('🌐 Page is about to be unloaded - form was submitted');
        });
        
        // السماح للـ form بالإرسال
        console.log('✅ Form submission allowed');
        return true;
    });
});
</script>
@endsection
