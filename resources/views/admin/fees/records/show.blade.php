@extends('layouts.admin')

@section('title', 'تفاصيل سجل المصروفات')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">تفاصيل سجل المصروفات</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.fees.records.index') }}">سجلات المصروفات</a></li>
        <li class="breadcrumb-item active">تفاصيل السجل #{{ $feeRecord->id }}</li>
    </ol>

    <div class="row">
        <!-- معلومات الطالب والسجل -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user-graduate me-1"></i>
                    معلومات الطالب والسجل
                    <div class="float-end">
                        <a href="{{ route('admin.fees.records.edit', $feeRecord->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit me-1"></i> تعديل السجل
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">معلومات الطالب</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold">اسم الطالب:</td>
                                    <td>{{ $feeRecord->student->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">المرحلة الدراسية:</td>
                                    <td>{{ $feeRecord->student->grade_level }} {{ $feeRecord->student->grade }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">رقم الطالب:</td>
                                    <td>{{ $feeRecord->student->student_number ?? 'غير محدد' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">معلومات السجل</h6>
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold">العام الدراسي:</td>
                                    <td>{{ $feeRecord->academic_year }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">الفصل الدراسي:</td>
                                    <td>{{ $feeRecord->semester }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">خطة الدفع:</td>
                                    <td>{{ $feeRecord->feePlan->plan_name ?? 'غير محدد' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">تاريخ الإنشاء:</td>
                                    <td>{{ $feeRecord->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- تفاصيل المصروفات -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-money-bill-wave me-1"></i>
                    تفاصيل المصروفات
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold">المصروفات الأساسية:</td>
                                    <td class="text-end">{{ number_format($feeRecord->basic_fees, 2) }} ج.م</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">رسوم التسجيل:</td>
                                    <td class="text-end">{{ number_format($feeRecord->registration_fees, 2) }} ج.م</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">رسوم الأنشطة:</td>
                                    <td class="text-end">{{ number_format($feeRecord->activities_fees, 2) }} ج.م</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">رسوم الباص:</td>
                                    <td class="text-end">{{ number_format($feeRecord->bus_fees, 2) }} ج.م</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">رسوم الكتب:</td>
                                    <td class="text-end">{{ number_format($feeRecord->books_fees, 2) }} ج.م</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold">رسوم الامتحانات:</td>
                                    <td class="text-end">{{ number_format($feeRecord->exam_fees, 2) }} ج.م</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">رسوم المنصة التعليمية:</td>
                                    <td class="text-end">{{ number_format($feeRecord->platform_fees, 2) }} ج.م</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">رسوم التأمين:</td>
                                    <td class="text-end">{{ number_format($feeRecord->insurance_fees, 2) }} ج.م</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">رسوم الخدمات:</td>
                                    <td class="text-end">{{ number_format($feeRecord->service_fees, 2) }} ج.م</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">رسوم أخرى:</td>
                                    <td class="text-end">{{ number_format($feeRecord->other_fees, 2) }} ج.م</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($feeRecord->other_fees_description)
                        <div class="mt-3">
                            <strong>وصف الرسوم الأخرى:</strong>
                            <p class="text-muted">{{ $feeRecord->other_fees_description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- الزي المدرسي -->
            @if($feeRecord->studentUniformItems->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-tshirt me-1"></i>
                        الزي المدرسي
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>قطعة الزي</th>
                                        <th>النوع</th>
                                        <th>الكمية</th>
                                        <th>السعر الوحدة</th>
                                        <th>الإجمالي</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($feeRecord->studentUniformItems as $uniformItem)
                                        <tr>
                                            <td>{{ $uniformItem->uniformItem->name }}</td>
                                            <td>{{ $uniformItem->uniformItem->type }}</td>
                                            <td>{{ $uniformItem->quantity }}</td>
                                            <td>{{ number_format($uniformItem->price, 2) }} ج.م</td>
                                            <td>{{ number_format($uniformItem->total_price, 2) }} ج.م</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- الأقساط -->
            @if($feeRecord->is_installment && $feeRecord->installments->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-calendar-alt me-1"></i>
                        تفاصيل الأقساط
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>رقم القسط</th>
                                        <th>اسم القسط</th>
                                        <th>المبلغ</th>
                                        <th>المبلغ المدفوع</th>
                                        <th>المتبقي</th>
                                        <th>تاريخ الاستحقاق</th>
                                        <th>الحالة</th>
                                        <th>سبب التخصيص</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($feeRecord->installments as $installment)
                                        <tr>
                                            <td>{{ $installment->installment_number }}</td>
                                            <td>{{ $installment->installment_name }}</td>
                                            <td>
                                                {{ number_format($installment->amount, 2) }} ج.م
                                                @if($installment->is_custom_amount)
                                                    <span class="badge bg-info ms-1">مخصص</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($installment->paid_amount, 2) }} ج.م</td>
                                            <td>{{ number_format($installment->remaining_amount, 2) }} ج.م</td>
                                            <td>{{ $installment->due_date ? $installment->due_date->format('Y-m-d') : 'غير محدد' }}</td>
                                            <td>
                                                @if($installment->status === 'مدفوع كاملاً')
                                                    <span class="badge bg-success">{{ $installment->status }}</span>
                                                @elseif($installment->status === 'مدفوع جزئياً')
                                                    <span class="badge bg-warning">{{ $installment->status }}</span>
                                                @elseif($installment->status === 'متأخر')
                                                    <span class="badge bg-danger">{{ $installment->status }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $installment->status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($installment->is_custom_amount && $installment->custom_amount_reason)
                                                    <small class="text-muted">{{ $installment->custom_amount_reason }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- ملخص المالي -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calculator me-1"></i>
                    الملخص المالي
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <div class="border rounded p-3 bg-light">
                                <h6 class="text-muted mb-1">إجمالي المصروفات</h6>
                                <h4 class="text-primary mb-0">{{ number_format($feeRecord->total_fees, 2) }} ج.م</h4>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="border rounded p-3 bg-light">
                                <h6 class="text-muted mb-1">المبلغ المدفوع</h6>
                                <h4 class="text-success mb-0">{{ number_format($feeRecord->total_paid, 2) }} ج.م</h4>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="border rounded p-3 bg-light">
                                <h6 class="text-muted mb-1">المبلغ المتبقي</h6>
                                <h4 class="text-warning mb-0">{{ number_format($feeRecord->remaining_amount, 2) }} ج.م</h4>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <strong>حالة الدفع:</strong>
                        @if($feeRecord->payment_status === 'مدفوع كاملاً')
                            <span class="badge bg-success ms-2">{{ $feeRecord->payment_status }}</span>
                        @elseif($feeRecord->payment_status === 'مدفوع جزئياً')
                            <span class="badge bg-warning ms-2">{{ $feeRecord->payment_status }}</span>
                        @elseif($feeRecord->payment_status === 'متأخر')
                            <span class="badge bg-danger ms-2">{{ $feeRecord->payment_status }}</span>
                        @else
                            <span class="badge bg-secondary ms-2">{{ $feeRecord->payment_status }}</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <strong>نوع الدفع:</strong>
                        @if($feeRecord->is_installment)
                            <span class="badge bg-info ms-2">أقساط ({{ $feeRecord->installments_count }})</span>
                        @else
                            <span class="badge bg-secondary ms-2">دفعة واحدة</span>
                        @endif
                    </div>

                    @if($feeRecord->is_installment && $feeRecord->down_payment > 0)
                        <div class="mb-3">
                            <strong>الدفعة المقدمة:</strong>
                            <span class="text-success">{{ number_format($feeRecord->down_payment, 2) }} ج.م</span>
                        </div>
                    @endif

                    @if($feeRecord->due_date)
                        <div class="mb-3">
                            <strong>تاريخ الاستحقاق:</strong>
                            <span class="text-muted">{{ $feeRecord->due_date->format('Y-m-d') }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- معلومات إضافية -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    معلومات إضافية
                </div>
                <div class="card-body">
                    @if($feeRecord->notes)
                        <div class="mb-3">
                            <strong>ملاحظات:</strong>
                            <p class="text-muted mt-2">{{ $feeRecord->notes }}</p>
                        </div>
                    @endif

                    <div class="mb-2">
                        <strong>تم الإنشاء بواسطة:</strong>
                        <span class="text-muted">{{ $feeRecord->created_by ?? 'النظام' }}</span>
                    </div>

                    @if($feeRecord->updated_by)
                        <div class="mb-2">
                            <strong>آخر تحديث بواسطة:</strong>
                            <span class="text-muted">{{ $feeRecord->updated_by }}</span>
                        </div>
                    @endif

                    <div class="mb-2">
                        <strong>تاريخ آخر تحديث:</strong>
                        <span class="text-muted">{{ $feeRecord->updated_at->format('Y-m-d H:i') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- أزرار الإجراءات -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <a href="{{ route('admin.fees.records.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> العودة للقائمة
                    </a>
                    <a href="{{ route('admin.fees.records.edit', $feeRecord->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> تعديل السجل
                    </a>
                    <button type="button" class="btn btn-success">
                        <i class="fas fa-print me-1"></i> طباعة
                    </button>
                    <button type="button" class="btn btn-info">
                        <i class="fas fa-download me-1"></i> تصدير PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection