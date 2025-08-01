@extends('layouts.admin')

@section('title', 'إدارة الزي المدرسي')

@section('content')
<div class="container mx-auto px-4 py-5">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">إدارة الزي المدرسي</h1>
        <a href="{{ route('admin.uniforms.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-plus ml-1"></i> إضافة قطعة جديدة
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    <!-- فلاتر البحث -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <h2 class="text-lg font-semibold mb-3">فلترة النتائج</h2>
        <form action="{{ route('admin.uniforms.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="grade_level" class="block text-sm font-medium text-gray-700 mb-1">المرحلة الدراسية</label>
                <select id="grade_level" name="grade_level" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">جميع المراحل</option>
                    <option value="primary" {{ request('grade_level') == 'primary' ? 'selected' : '' }}>ابتدائي</option>
                    <option value="preparatory" {{ request('grade_level') == 'preparatory' ? 'selected' : '' }}>إعدادي</option>
                    <option value="secondary" {{ request('grade_level') == 'secondary' ? 'selected' : '' }}>ثانوي</option>
                </select>
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">نوع الزي</label>
                <select id="type" name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">جميع الأنواع</option>
                    <option value="صيفي" {{ request('type') == 'صيفي' ? 'selected' : '' }}>صيفي</option>
                    <option value="شتوي" {{ request('type') == 'شتوي' ? 'selected' : '' }}>شتوي</option>
                    <option value="رياضي" {{ request('type') == 'رياضي' ? 'selected' : '' }}>رياضي</option>
                    <option value="موحد" {{ request('type') == 'موحد' ? 'selected' : '' }}>موحد</option>
                </select>
            </div>
            <div>
                <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">الجنس</label>
                <select id="gender" name="gender" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <option value="">الجميع</option>
                    <option value="ذكر" {{ request('gender') == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                    <option value="أنثى" {{ request('gender') == 'أنثى' ? 'selected' : '' }}>أنثى</option>
                    <option value="الجميع" {{ request('gender') == 'الجميع' ? 'selected' : '' }}>الجميع</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-search ml-1"></i> بحث
                </button>
                <a href="{{ route('admin.uniforms.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded mr-2">
                    <i class="fas fa-redo ml-1"></i> إعادة تعيين
                </a>
            </div>
        </form>
    </div>

    <!-- جدول قطع الزي المدرسي -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        الاسم
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        النوع
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        الجنس
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        المرحلة
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        السعر
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        الحالة
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        الإجراءات
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($uniformItems as $item)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $item->name }}</div>
                        <div class="text-sm text-gray-500">{{ $item->description }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $item->type == 'صيفي' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $item->type == 'شتوي' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $item->type == 'رياضي' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $item->type == 'موحد' ? 'bg-purple-100 text-purple-800' : '' }}">
                            {{ $item->type }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $item->gender }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($item->grade_level == 'primary')
                            <span>ابتدائي</span>
                            @if($item->grade)
                                <span class="text-xs text-gray-500">({{ $item->grade }})</span>
                            @endif
                        @elseif($item->grade_level == 'preparatory')
                            <span>إعدادي</span>
                            @if($item->grade)
                                <span class="text-xs text-gray-500">({{ $item->grade }})</span>
                            @endif
                        @elseif($item->grade_level == 'secondary')
                            <span>ثانوي</span>
                            @if($item->grade)
                                <span class="text-xs text-gray-500">({{ $item->grade }})</span>
                            @endif
                        @else
                            {{ $item->grade_level }}
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div class="price-display" data-item-id="{{ $item->id }}">
                            <span class="font-semibold">{{ number_format($item->price, 2) }}</span> جنيه
                        </div>
                        <div class="price-edit hidden" data-item-id="{{ $item->id }}">
                            <div class="flex items-center">
                                <input type="number" step="0.01" min="0" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm" value="{{ $item->price }}" id="price-input-{{ $item->id }}">
                                <button type="button" class="save-price-btn ml-1 text-green-600 hover:text-green-800" data-item-id="{{ $item->id }}">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" class="cancel-price-btn ml-1 text-red-600 hover:text-red-800" data-item-id="{{ $item->id }}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($item->is_active)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                نشط
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                غير نشط
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <div class="flex space-x-2 space-x-reverse">
                            <button type="button" class="text-blue-600 hover:text-blue-900 edit-price-btn" data-item-id="{{ $item->id }}">
                                <i class="fas fa-dollar-sign"></i>
                            </button>
                            <a href="{{ route('admin.uniforms.edit', $item->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.uniforms.toggle-active', $item->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-{{ $item->is_active ? 'yellow' : 'green' }}-600 hover:text-{{ $item->is_active ? 'yellow' : 'green' }}-900">
                                    <i class="fas fa-{{ $item->is_active ? 'ban' : 'check' }}"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.uniforms.destroy', $item->id) }}" method="POST" class="inline delete-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        لا توجد قطع زي مدرسي مسجلة
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- ترقيم الصفحات -->
    <div class="mt-4">
        {{ $uniformItems->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // تأكيد الحذف
        const deleteForms = document.querySelectorAll('.delete-form');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (confirm('هل أنت متأكد من حذف هذه القطعة؟')) {
                    this.submit();
                }
            });
        });

        // تعديل السعر
        const editPriceBtns = document.querySelectorAll('.edit-price-btn');
        const savePriceBtns = document.querySelectorAll('.save-price-btn');
        const cancelPriceBtns = document.querySelectorAll('.cancel-price-btn');

        editPriceBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const itemId = this.getAttribute('data-item-id');
                document.querySelector(`.price-display[data-item-id="${itemId}"]`).classList.add('hidden');
                document.querySelector(`.price-edit[data-item-id="${itemId}"]`).classList.remove('hidden');
            });
        });

        cancelPriceBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const itemId = this.getAttribute('data-item-id');
                document.querySelector(`.price-display[data-item-id="${itemId}"]`).classList.remove('hidden');
                document.querySelector(`.price-edit[data-item-id="${itemId}"]`).classList.add('hidden');
            });
        });

        savePriceBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const itemId = this.getAttribute('data-item-id');
                const newPrice = document.getElementById(`price-input-${itemId}`).value;
                
                // إرسال طلب AJAX لتحديث السعر
                fetch(`{{ url('admin/uniforms') }}/${itemId}/update-price`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ price: newPrice })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // تحديث العرض
                        document.querySelector(`.price-display[data-item-id="${itemId}"] span`).textContent = parseFloat(data.new_price).toFixed(2);
                        document.querySelector(`.price-display[data-item-id="${itemId}"]`).classList.remove('hidden');
                        document.querySelector(`.price-edit[data-item-id="${itemId}"]`).classList.add('hidden');
                        
                        // إظهار رسالة نجاح
                        alert('تم تحديث السعر بنجاح');
                    } else {
                        alert(data.message || 'حدث خطأ أثناء تحديث السعر');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ أثناء تحديث السعر');
                });
            });
        });
    });
</script>
@endsection