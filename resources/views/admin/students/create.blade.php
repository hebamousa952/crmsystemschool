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
            <form action="{{ route('admin.students.store') }}" method="POST" class="p-8">
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
</script>
@endsection

@section('scripts')
<script>
// بيانات المراحل والصفوف والفصول
const gradesData = {
    primary: {
        name: "المرحلة الابتدائية",
        grades: {
            "grade_1": {
                name: "الصف الأول الابتدائي",
                classrooms: ["1A", "1B", "1C", "1D"]
            },
            "grade_2": {
                name: "الصف الثاني الابتدائي",
                classrooms: ["2A", "2B", "2C", "2D", "2E"]
            },
            "grade_3": {
                name: "الصف الثالث الابتدائي",
                classrooms: ["3A", "3B", "3C", "3D"]
            },
            "grade_4": {
                name: "الصف الرابع الابتدائي",
                classrooms: ["4A", "4B", "4C", "4D"]
            },
            "grade_5": {
                name: "الصف الخامس الابتدائي",
                classrooms: ["5A", "5B", "5C", "5D"]
            },
            "grade_6": {
                name: "الصف السادس الابتدائي",
                classrooms: ["6A", "6B"]
            }
        }
    },
    preparatory: {
        name: "المرحلة الإعدادية",
        grades: {
            "prep_1": {
                name: "الصف الأول الإعدادي",
                classrooms: ["1A PRE", "1B PRE"]
            },
            "prep_2": {
                name: "الصف الثاني الإعدادي",
                classrooms: ["2A PRE", "2B PRE"]
            },
            "prep_3": {
                name: "الصف الثالث الإعدادي",
                classrooms: ["3A PRE", "3B PRE"]
            }
        }
    }
};

// تحديث الصفوف عند اختيار المرحلة
function updateGrades() {
    const gradeLevelSelect = document.getElementById('grade_level');
    const gradeSelect = document.getElementById('grade');
    const classroomSelect = document.getElementById('classroom');

    const selectedLevel = gradeLevelSelect.value;

    // إعادة تعيين الصفوف والفصول
    gradeSelect.innerHTML = '<option value="">اختر الصف الدراسي</option>';
    classroomSelect.innerHTML = '<option value="">اختر الفصل</option>';
    classroomSelect.disabled = true;

    if (selectedLevel && gradesData[selectedLevel]) {
        // تفعيل dropdown الصفوف
        gradeSelect.disabled = false;

        // إضافة الصفوف للمرحلة المختارة
        const grades = gradesData[selectedLevel].grades;
        for (const [gradeKey, gradeData] of Object.entries(grades)) {
            const option = document.createElement('option');
            option.value = gradeKey;
            option.textContent = gradeData.name;
            gradeSelect.appendChild(option);
        }
    } else {
        gradeSelect.disabled = true;
    }
}

// تحديث الفصول عند اختيار الصف
function updateClassrooms() {
    const gradeLevelSelect = document.getElementById('grade_level');
    const gradeSelect = document.getElementById('grade');
    const classroomSelect = document.getElementById('classroom');

    const selectedLevel = gradeLevelSelect.value;
    const selectedGrade = gradeSelect.value;

    classroomSelect.innerHTML = '<option value="">اختر الفصل</option>';

    if (selectedLevel && selectedGrade && gradesData[selectedLevel].grades[selectedGrade]) {
        classroomSelect.disabled = false;

        const classrooms = gradesData[selectedLevel].grades[selectedGrade].classrooms;
        classrooms.forEach(classroom => {
            const option = document.createElement('option');
            option.value = classroom;
            option.textContent = `فصل ${classroom}`;
            classroomSelect.appendChild(option);
        });
    } else {
        classroomSelect.disabled = true;
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

// تشغيل الوظيفة عند تحميل الصفحة للحفاظ على الحالة
document.addEventListener('DOMContentLoaded', function() {
    toggleLegalGuardianFields();
});
</script>
@endsection
