@extends('layouts.admin')

@section('title', 'سجلات المصروفات')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">سجلات المصروفات</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
        <li class="breadcrumb-item active">سجلات المصروفات</li>
    </ol>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-money-bill-wave me-1"></i>
            قائمة سجلات المصروفات
            <div class="float-end">
                <a href="{{ route('admin.fees.records.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> إضافة سجل جديد
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($feeRecords->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>الطالب</th>
                                <th>العام الدراسي</th>
                                <th>الفصل الدراسي</th>
                                <th>إجمالي المصروفات</th>
                                <th>المبلغ المدفوع</th>
                                <th>المبلغ المتبقي</th>
                                <th>حالة الدفع</th>
                                <th>نوع الدفع</th>
                                <th>تاريخ الإنشاء</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($feeRecords as $record)
                                <tr>
                                    <td>{{ $record->id }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $record->student->name }}</div>
                                        <small class="text-muted">{{ $record->student->grade_level }} {{ $record->student->grade }}</small>
                                    </td>
                                    <td>{{ $record->academic_year }}</td>
                                    <td>{{ $record->semester }}</td>
                                    <td>
                                        <span class="fw-bold text-primary">{{ number_format($record->total_fees, 2) }} ج.م</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">{{ number_format($record->total_paid, 2) }} ج.م</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-warning">{{ number_format($record->remaining_amount, 2) }} ج.م</span>
                                    </td>
                                    <td>
                                        @if($record->payment_status === 'مدفوع كاملاً')
                                            <span class="badge bg-success">{{ $record->payment_status }}</span>
                                        @elseif($record->payment_status === 'مدفوع جزئياً')
                                            <span class="badge bg-warning">{{ $record->payment_status }}</span>
                                        @elseif($record->payment_status === 'متأخر')
                                            <span class="badge bg-danger">{{ $record->payment_status }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $record->payment_status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->is_installment)
                                            <span class="badge bg-info">أقساط</span>
                                            <small class="d-block text-muted">{{ $record->installments_count }} قسط</small>
                                        @else
                                            <span class="badge bg-secondary">دفعة واحدة</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $record->created_at->format('Y-m-d') }}</div>
                                        <small class="text-muted">{{ $record->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.fees.records.show', $record->id) }}" 
                                               class="btn btn-sm btn-outline-info" title="عرض التفاصيل">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.fees.records.edit', $record->id) }}" 
                                               class="btn btn-sm btn-outline-primary" title="تعديل">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="confirmDelete({{ $record->id }})" title="حذف">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $feeRecords->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">لا توجد سجلات مصروفات</h5>
                    <p class="text-muted">لم يتم إنشاء أي سجلات مصروفات بعد</p>
                    <a href="{{ route('admin.fees.records.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> إضافة أول سجل مصروفات
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal تأكيد الحذف -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تأكيد الحذف</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>هل أنت متأكد من حذف سجل المصروفات هذا؟</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>تحذير:</strong> سيتم حذف جميع الأقساط والمدفوعات المرتبطة بهذا السجل نهائياً.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">حذف نهائياً</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function confirmDelete(recordId) {
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `{{ route('admin.fees.records.index') }}/${recordId}`;
        
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
</script>
@endsection