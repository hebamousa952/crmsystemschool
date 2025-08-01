@extends('layouts.admin')

@section('title', 'تفاصيل قطعة الزي المدرسي')

@section('content')
<div class="container mx-auto px-4 py-5">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">تفاصيل قطعة الزي المدرسي</h1>
        <div class="flex space-x-2 space-x-reverse">
            <a href="{{ route('admin.uniforms.edit', $uniformItem->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-edit ml-1"></i> تعديل
            </a>
            <a href="{{ route('admin.uniforms.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-arrow-right ml-1"></i> العودة للقائمة
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">معلومات أساسية</h2>
                    <div class="space-y-3">
                        <div>
                            <span class="text-gray-600 font-medium">الاسم:</span>
                            <span class="text-gray-800 mr-2">{{ $uniformItem->name }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 font-medium">النوع:</span>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $uniformItem->type == 'صيفي' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $uniformItem->type == 'شتوي' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $uniformItem->type == 'رياضي' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $uniformItem->type == 'موحد' ? 'bg-purple-100 text-purple-800' : '' }}">
                                {{ $uniformItem->type }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-600 font-medium">الجنس:</span>
                            <span class="text-gray-800 mr-2">{{ $uniformItem->gender }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 font-medium">السعر:</span>
                            <span class="text-gray-800 mr-2">{{ number_format($uniformItem->price, 2) }} جنيه</span>
                        </div>
                        <div>
                            <span class="text-gray-600 font-medium">الحالة:</span>
                            @if($uniformItem->is_active)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    نشط
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    غير نشط
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">معلومات إضافية</h2>
                    <div class="space-y-3">
                        <div>
                            <span class="text-gray-600 font-medium">المرحلة الدراسية:</span>
                            <span class="text-gray-800 mr-2">
                                @if($uniformItem->grade_level == 'primary')
                                    ابتدائي
                                @elseif($uniformItem->grade_level == 'preparatory')
                                    إعدادي
                                @elseif($uniformItem->grade_level == 'secondary')
                                    ثانوي
                                @else
                                    {{ $uniformItem->grade_level }}
                                @endif
                            </span>
                        </div>
                        @if($uniformItem->grade)
                        <div>
                            <span class="text-gray-600 font-medium">الصف الدراسي:</span>
                            <span class="text-gray-800 mr-2">{{ $uniformItem->grade }}</span>
                        </div>
                        @endif
                        <div>
                            <span class="text-gray-600 font-medium">الوصف:</span>
                            <p class="text-gray-800 mt-1">{{ $uniformItem->description ?: 'لا يوجد وصف' }}</p>
                        </div>
                        <div>
                            <span class="text-gray-600 font-medium">تاريخ الإنشاء:</span>
                            <span class="text-gray-800 mr-2">{{ $uniformItem->created_at->format('Y-m-d H:i') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 font-medium">آخر تحديث:</span>
                            <span class="text-gray-800 mr-2">{{ $uniformItem->updated_at->format('Y-m-d H:i') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600 font-medium">تم الإنشاء بواسطة:</span>
                            <span class="text-gray-800 mr-2">{{ $uniformItem->created_by ?: 'غير معروف' }}</span>
                        </div>
                        @if($uniformItem->updated_by)
                        <div>
                            <span class="text-gray-600 font-medium">تم التحديث بواسطة:</span>
                            <span class="text-gray-800 mr-2">{{ $uniformItem->updated_by }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @if(isset($studentCount) && $studentCount > 0)
            <div class="mt-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">إحصائيات الاستخدام</h2>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="text-gray-700">
                        <span class="font-medium">عدد الطلاب المستخدمين لهذه القطعة:</span>
                        <span class="mr-2">{{ $studentCount }} طالب</span>
                    </div>
                </div>
            </div>
            @endif

            <div class="mt-6 flex justify-end">
                <form action="{{ route('admin.uniforms.destroy', $uniformItem->id) }}" method="POST" class="inline delete-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-trash ml-1"></i> حذف
                    </button>
                </form>
                <form action="{{ route('admin.uniforms.toggle-active', $uniformItem->id) }}" method="POST" class="inline mr-2">
                    @csrf
                    <button type="submit" class="bg-{{ $uniformItem->is_active ? 'yellow' : 'green' }}-600 hover:bg-{{ $uniformItem->is_active ? 'yellow' : 'green' }}-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-{{ $uniformItem->is_active ? 'ban' : 'check' }} ml-1"></i> {{ $uniformItem->is_active ? 'تعطيل' : 'تفعيل' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // تأكيد الحذف
        const deleteForm = document.querySelector('.delete-form');
        if (deleteForm) {
            deleteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                if (confirm('هل أنت متأكد من حذف هذه القطعة؟')) {
                    this.submit();
                }
            });
        }
    });
</script>
@endsection