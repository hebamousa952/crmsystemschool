@extends('layouts.admin')

@section('title', 'تعديل قطعة زي مدرسي')

@section('content')
<div class="container mx-auto px-4 py-5">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">تعديل قطعة زي مدرسي</h1>
        <a href="{{ route('admin.uniforms.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-arrow-right ml-1"></i> العودة للقائمة
        </a>
    </div>

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.uniforms.update', $uniformItem->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- اسم القطعة -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">اسم القطعة <span class="text-red-600">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $uniformItem->name) }}" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('name') border-red-500 @enderror">
                    @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- نوع الزي -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">نوع الزي <span class="text-red-600">*</span></label>
                    <select name="type" id="type" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('type') border-red-500 @enderror">
                        <option value="">اختر النوع</option>
                        <option value="صيفي" {{ old('type', $uniformItem->type) == 'صيفي' ? 'selected' : '' }}>صيفي</option>
                        <option value="شتوي" {{ old('type', $uniformItem->type) == 'شتوي' ? 'selected' : '' }}>شتوي</option>
                        <option value="رياضي" {{ old('type', $uniformItem->type) == 'رياضي' ? 'selected' : '' }}>رياضي</option>
                        <option value="موحد" {{ old('type', $uniformItem->type) == 'موحد' ? 'selected' : '' }}>موحد</option>
                    </select>
                    @error('type')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- الجنس -->
                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">الجنس <span class="text-red-600">*</span></label>
                    <select name="gender" id="gender" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('gender') border-red-500 @enderror">
                        <option value="">اختر الجنس</option>
                        <option value="ذكر" {{ old('gender', $uniformItem->gender) == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                        <option value="أنثى" {{ old('gender', $uniformItem->gender) == 'أنثى' ? 'selected' : '' }}>أنثى</option>
                        <option value="الجميع" {{ old('gender', $uniformItem->gender) == 'الجميع' ? 'selected' : '' }}>الجميع</option>
                    </select>
                    @error('gender')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- السعر -->
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">السعر <span class="text-red-600">*</span></label>
                    <div class="relative rounded-md shadow-sm">
                        <input type="number" name="price" id="price" value="{{ old('price', $uniformItem->price) }}" step="0.01" min="0" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('price') border-red-500 @enderror">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">جنيه</span>
                        </div>
                    </div>
                    @error('price')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- المرحلة الدراسية -->
                <div>
                    <label for="grade_level" class="block text-sm font-medium text-gray-700 mb-1">المرحلة الدراسية <span class="text-red-600">*</span></label>
                    <select name="grade_level" id="grade_level" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('grade_level') border-red-500 @enderror">
                        <option value="">اختر المرحلة</option>
                        <option value="primary" {{ old('grade_level', $uniformItem->grade_level) == 'primary' ? 'selected' : '' }}>ابتدائي</option>
                        <option value="preparatory" {{ old('grade_level', $uniformItem->grade_level) == 'preparatory' ? 'selected' : '' }}>إعدادي</option>
                        <option value="secondary" {{ old('grade_level', $uniformItem->grade_level) == 'secondary' ? 'selected' : '' }}>ثانوي</option>
                    </select>
                    @error('grade_level')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- الصف الدراسي (اختياري) -->
                <div>
                    <label for="grade" class="block text-sm font-medium text-gray-700 mb-1">الصف الدراسي (اختياري)</label>
                    <input type="text" name="grade" id="grade" value="{{ old('grade', $uniformItem->grade) }}"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('grade') border-red-500 @enderror">
                    @error('grade')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- الوصف -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">الوصف</label>
                    <textarea name="description" id="description" rows="3"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 @error('description') border-red-500 @enderror">{{ old('description', $uniformItem->description) }}</textarea>
                    @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- الحالة -->
                <div class="md:col-span-2">
                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $uniformItem->is_active) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <label for="is_active" class="mr-2 block text-sm text-gray-700">نشط</label>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-save ml-1"></i> حفظ التغييرات
                </button>
                <a href="{{ route('admin.uniforms.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded mr-2">
                    <i class="fas fa-times ml-1"></i> إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
@endsection