@extends('layouts.admin')

@section('title', 'تعديل سجل المصروفات')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">تعديل سجل المصروفات</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">لوحة التحكم</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.fees.records.index') }}">سجلات المصروفات</a></li>
        <li class="breadcrumb-item active">تعديل السجل #{{ $feeRecord->id }}</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            تعديل بيانات سجل المصروفات
        </div>
        <div class="card-body">
            <form action="{{ route('admin.fees.records.update', $feeRecord->id) }}" method="POST" id="feeRecordForm">
                @csrf
                @method('PUT')
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="student_id" class="form-label">الطالب <span class="text-danger">*</span></label>
                            <select name="student_id" id="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
                                <option value="">-- اختر الطالب --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ old('student_id', $feeRecord->student_id) == $student->id ? 'selected' : '' }}>
                                        {{ $student->name }} - {{ $student->grade_level }} {{ $student->grade }}
                                    </option>
                                @endforeach
                            </select>
                            @error('student_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="academic_year" class="form-label">العام الدراسي <span class="text-danger">*</span></label>
                            <input type="text" name="academic_year" id="academic_year" class="form-control @error('academic_year') is-invalid @enderror" 
                                value="{{ old('academic_year', $feeRecord->academic_year) }}" required>
                            @error('academic_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group mb-3">
                            <label for="semester" class="form-label">الفصل الدراسي <span class="text-danger">*</span></label>
                            <select name="semester" id="semester" class="form-select @error('semester') is-invalid @enderror" required>
                                <option value="الفصل الأول" {{ old('semester', $feeRecord->semester) == 'الفصل الأول' ? 'selected' : '' }}>الفصل الأول</option>
                                <option value="الفصل الثاني" {{ old('semester', $feeRecord->semester) == 'الفصل الثاني' ? 'selected' : '' }}>الفصل الثاني</option>
                                <option value="العام الكامل" {{ old('semester', $feeRecord->semester) == 'العام الكامل' ? 'selected' : '' }}>العام الكامل</option>
                            </select>
                            @error('semester')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group mb-3">
                            <label for="fee_plan_id" class="form-label">خطة الدفع</label>
                            <select name="fee_plan_id" id="fee_plan_id" class="form-select @error('fee_plan_id') is-invalid @enderror">
                                <option value="">-- اختر خطة الدفع --</option>
                                @foreach($feePlans as $plan)
                                    <option value="{{ $plan->id }}" {{ old('fee_plan_id', $feeRecord->fee_plan_id) == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->plan_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('fee_plan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <h4 class="mt-4 mb-3">تفاصيل المصروفات</h4>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="basic_fees" class="form-label">المصروفات الأساسية <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="basic_fees" id="basic_fees" class="form-control fee-input @error('basic_fees') is-invalid @enderror" 
                                value="{{ old('basic_fees', $feeRecord->basic_fees) }}" required>
                            @error('basic_fees')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="registration_fees" class="form-label">رسوم التسجيل</label>
                            <input type="number" step="0.01" name="registration_fees" id="registration_fees" class="form-control fee-input @error('registration_fees') is-invalid @enderror" 
                                value="{{ old('registration_fees', $feeRecord->registration_fees) }}">
                            @error('registration_fees')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="activities_fees" class="form-label">رسوم الأنشطة</label>
                            <input type="number" step="0.01" name="activities_fees" id="activities_fees" class="form-control fee-input @error('activities_fees') is-invalid @enderror" 
                                value="{{ old('activities_fees', $feeRecord->activities_fees) }}">
                            @error('activities_fees')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="bus_fees" class="form-label">رسوم الباص</label>
                            <input type="number" step="0.01" name="bus_fees" id="bus_fees" class="form-control fee-input @error('bus_fees') is-invalid @enderror" 
                                value="{{ old('bus_fees', $feeRecord->bus_fees) }}">
                            @error('bus_fees')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="books_fees" class="form-label">رسوم الكتب</label>
                            <input type="number" step="0.01" name="books_fees" id="books_fees" class="form-control fee-input @error('books_fees') is-invalid @enderror" 
                                value="{{ old('books_fees', $feeRecord->books_fees) }}">
                            @error('books_fees')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="exam_fees" class="form-label">رسوم الامتحانات</label>
                            <input type="number" step="0.01" name="exam_fees" id="exam_fees" class="form-control fee-input @error('exam_fees') is-invalid @enderror" 
                                value="{{ old('exam_fees', $feeRecord->exam_fees) }}">
                            @error('exam_fees')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="platform_fees" class="form-label">رسوم المنصة التعليمية</label>
                            <input type="number" step="0.01" name="platform_fees" id="platform_fees" class="form-control fee-input @error('platform_fees') is-invalid @enderror" 
                                value="{{ old('platform_fees', $feeRecord->platform_fees) }}">
                            @error('platform_fees')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="insurance_fees" class="form-label">رسوم التأمين</label>
                            <input type="number" step="0.01" name="insurance_fees" id="insurance_fees" class="form-control fee-input @error('insurance_fees') is-invalid @enderror" 
                                value="{{ old('insurance_fees', $feeRecord->insurance_fees) }}">
                            @error('insurance_fees')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="service_fees" class="form-label">رسوم الخدمات</label>
                            <input type="number" step="0.01" name="service_fees" id="service_fees" class="form-control fee-input @error('service_fees') is-invalid @enderror" 
                                value="{{ old('service_fees', $feeRecord->service_fees) }}">
                            @error('service_fees')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="other_fees" class="form-label">رسوم أخرى</label>
                            <input type="number" step="0.01" name="other_fees" id="other_fees" class="form-control fee-input @error('other_fees') is-invalid @enderror" 
                                value="{{ old('other_fees', $feeRecord->other_fees) }}">
                            @error('other_fees')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="form-group mb-3">
                            <label for="other_fees_description" class="form-label">وصف الرسوم الأخرى</label>
                            <input type="text" name="other_fees_description" id="other_fees_description" class="form-control @error('other_fees_description') is-invalid @enderror" 
                                value="{{ old('other_fees_description', $feeRecord->other_fees_description) }}">
                            @error('other_fees_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-group mb-3">
                            <label for="total_fees" class="form-label">إجمالي المصروفات</label>
                            <input type="number" step="0.01" id="total_fees" class="form-control" readonly>
                        </div>
                    </div>
                </div>
                
                <!-- الزي المدرسي -->
                <h4 class="mt-4 mb-3">الزي المدرسي</h4>
                <div class="uniform-items-container">
                    @if($feeRecord->studentUniformItems->count() > 0)
                        @foreach($feeRecord->studentUniformItems as $index => $uniformItem)
                            <div class="row mb-3 uniform-item">
                                <div class="col-md-5">
                                    <div class="form-group mb-3">
                                        <label class="form-label">قطعة الزي</label>
                                        <select name="uniform_item_ids[]" class="form-select uniform-item-select">
                                            <option value="">-- اختر قطعة الزي --</option>
                                            @foreach($uniformItems as $item)
                                                <option value="{{ $item->id }}" data-price="{{ $item->price }}" 
                                                    {{ $uniformItem->uniform_item_id == $item->id ? 'selected' : '' }}>
                                                    {{ $item->name }} - {{ $item->type }} ({{ $item->price }} ج.م)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label">الكمية</label>
                                        <input type="number" name="uniform_quantities[]" class="form-control uniform-quantity" 
                                            value="{{ $uniformItem->quantity }}" min="1">
                                    </div>
                                </div>
                                
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label class="form-label">السعر (اختياري)</label>
                                        <input type="number" step="0.01" name="uniform_prices[]" class="form-control uniform-price" 
                                            value="{{ $uniformItem->price }}">
                                    </div>
                                </div>
                                
                                <div class="col-md-2">
                                    <div class="form-group mb-3">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-danger d-block remove-uniform-item">حذف</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="row mb-3 uniform-item">
                            <div class="col-md-5">
                                <div class="form-group mb-3">
                                    <label class="form-label">قطعة الزي</label>
                                    <select name="uniform_item_ids[]" class="form-select uniform-item-select">
                                        <option value="">-- اختر قطعة الزي --</option>
                                        @foreach($uniformItems as $item)
                                            <option value="{{ $item->id }}" data-price="{{ $item->price }}">
                                                {{ $item->name }} - {{ $item->type }} ({{ $item->price }} ج.م)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label class="form-label">الكمية</label>
                                    <input type="number" name="uniform_quantities[]" class="form-control uniform-quantity" value="1" min="1">
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">السعر (اختياري)</label>
                                    <input type="number" step="0.01" name="uniform_prices[]" class="form-control uniform-price">
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-danger d-block remove-uniform-item">حذف</button>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="row mb-3">
                    <div class="col-12">
                        <button type="button" class="btn btn-success add-uniform-item">
                            <i class="fas fa-plus me-1"></i> إضافة قطعة زي
                        </button>
                        <input type="hidden" name="update_uniform_items" value="1">
                    </div>
                </div>
                
                <!-- خيارات الدفع -->
                <h4 class="mt-4 mb-3">خيارات الدفع</h4>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_installment" name="is_installment" 
                                {{ old('is_installment', $feeRecord->is_installment) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_installment">تقسيط المصروفات</label>
                        </div>
                    </div>
                </div>
                
                <div id="installment-options" class="{{ $feeRecord->is_installment ? '' : 'd-none' }}">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="installments_count" class="form-label">عدد الأقساط</label>
                                <input type="number" name="installments_count" id="installments_count" class="form-control @error('installments_count') is-invalid @enderror" 
                                    value="{{ old('installments_count', $feeRecord->installments_count) }}" min="1" max="12">
                                @error('installments_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="down_payment" class="form-label">الدفعة المقدمة</label>
                                <input type="number" step="0.01" name="down_payment" id="down_payment" class="form-control @error('down_payment') is-invalid @enderror" 
                                    value="{{ old('down_payment', $feeRecord->down_payment) }}">
                                @error('down_payment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="installment_start_date" class="form-label">تاريخ بداية الأقساط</label>
                                <input type="date" name="installment_start_date" id="installment_start_date" class="form-control @error('installment_start_date') is-invalid @enderror" 
                                    value="{{ old('installment_start_date', $feeRecord->installment_start_date ? $feeRecord->installment_start_date->format('Y-m-d') : '') }}">
                                @error('installment_start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- تعديل الأقساط الموجودة -->
                    @if($feeRecord->installments->count() > 0)
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="edit_existing_installments" name="edit_existing_installments">
                                    <label class="form-check-label" for="edit_existing_installments">تعديل الأقساط الموجودة</label>
                                </div>
                            </div>
                        </div>
                        
                        <div id="existing-installments-container" class="d-none">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-1"></i> 
                                يمكنك تعديل مبالغ الأقساط وأسباب التخصيص. تأكد من أن مجموع الأقساط يساوي المبلغ المتبقي.
                            </div>
                            
                            @foreach($feeRecord->installments as $installment)
                                <div class="row mb-3 existing-installment">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label">القسط {{ $installment->installment_number }}</label>
                                            <input type="text" class="form-control" value="{{ $installment->installment_name }}" readonly>
                                            <input type="hidden" name="existing_installment_ids[]" value="{{ $installment->id }}">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-label">المبلغ الجديد</label>
                                            <input type="number" step="0.01" name="existing_installment_amounts[]" 
                                                class="form-control existing-installment-amount" 
                                                value="{{ $installment->amount }}" 
                                                data-original="{{ $installment->amount }}">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label class="form-label">سبب تخصيص المبلغ (اختياري)</label>
                                            <input type="text" name="existing_installment_reasons[]" 
                                                class="form-control" 
                                                value="{{ $installment->custom_amount_reason }}"
                                                placeholder="سبب تخصيص مبلغ القسط {{ $installment->installment_number }}">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label class="form-label">الحالة</label>
                                            <input type="text" class="form-control" value="{{ $installment->status }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                            <input type="hidden" name="update_installments" value="1">
                        </div>
                    @endif
                    
                    <!-- إنشاء أقساط جديدة -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="custom_installments" name="custom_installments">
                                <label class="form-check-label" for="custom_installments">إنشاء أقساط جديدة مخصصة</label>
                            </div>
                        </div>
                    </div>
                    
                    <div id="installment-amounts-container" class="d-none">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i> 
                            <strong>تحذير:</strong> إنشاء أقساط جديدة سيحل محل الأقساط الموجودة. تأكد من صحة البيانات قبل الحفظ.
                        </div>
                        
                        <div id="installment-amounts">
                            <!-- سيتم إنشاء حقول الأقساط ديناميكياً هنا -->
                        </div>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="form-group mb-3">
                            <label for="notes" class="form-label">ملاحظات</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $feeRecord->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> حفظ التعديلات
                        </button>
                        <a href="{{ route('admin.fees.records.show', $feeRecord->id) }}" class="btn btn-secondary">
                            <i class="fas fa-eye me-1"></i> عرض السجل
                        </a>
                        <a href="{{ route('admin.fees.records.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> إلغاء
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // حساب إجمالي المصروفات
        function calculateTotalFees() {
            let total = 0;
            $('.fee-input').each(function() {
                total += parseFloat($(this).val() || 0);
            });
            $('#total_fees').val(total.toFixed(2));
            
            updateInstallmentAmounts();
        }
        
        // تحديث مبالغ الأقساط
        function updateInstallmentAmounts() {
            if (!$('#custom_installments').is(':checked')) return;
            
            const totalFees = parseFloat($('#total_fees').val() || 0);
            const downPayment = parseFloat($('#down_payment').val() || 0);
            const remainingAmount = totalFees - downPayment;
            const installmentsCount = parseInt($('#installments_count').val() || 1);
            
            if (installmentsCount < 1) return;
            
            const equalAmount = (remainingAmount / installmentsCount).toFixed(2);
            
            $('.installment-amount').each(function(index) {
                $(this).val(equalAmount);
            });
        }
        
        // إنشاء حقول الأقساط الجديدة
        function createInstallmentFields() {
            const container = $('#installment-amounts');
            container.empty();
            
            const installmentsCount = parseInt($('#installments_count').val() || 1);
            const totalFees = parseFloat($('#total_fees').val() || 0);
            const downPayment = parseFloat($('#down_payment').val() || 0);
            const remainingAmount = totalFees - downPayment;
            const equalAmount = (remainingAmount / installmentsCount).toFixed(2);
            
            for (let i = 1; i <= installmentsCount; i++) {
                const row = $('<div class="row mb-3"></div>');
                
                // حقل مبلغ القسط
                const amountCol = $('<div class="col-md-4"></div>');
                const amountGroup = $('<div class="form-group"></div>');
                amountGroup.append(`<label class="form-label">القسط ${i}</label>`);
                amountGroup.append(`<input type="number" step="0.01" name="installment_amounts[]" class="form-control installment-amount" value="${equalAmount}">`);
                amountCol.append(amountGroup);
                
                // حقل سبب المبلغ المخصص
                const reasonCol = $('<div class="col-md-8"></div>');
                const reasonGroup = $('<div class="form-group"></div>');
                reasonGroup.append(`<label class="form-label">سبب تخصيص المبلغ (اختياري)</label>`);
                reasonGroup.append(`<input type="text" name="installment_reasons[]" class="form-control" placeholder="سبب تخصيص مبلغ القسط ${i}">`);
                reasonCol.append(reasonGroup);
                
                row.append(amountCol);
                row.append(reasonCol);
                container.append(row);
            }
        }
        
        // إضافة قطعة زي جديدة
        $('.add-uniform-item').on('click', function() {
            const template = $('.uniform-item').first().clone();
            template.find('input').val('');
            template.find('select').val('');
            template.find('.uniform-quantity').val(1);
            $('.uniform-items-container').append(template);
        });
        
        // حذف قطعة زي
        $(document).on('click', '.remove-uniform-item', function() {
            if ($('.uniform-item').length > 1) {
                $(this).closest('.uniform-item').remove();
            } else {
                $('.uniform-item').find('input').val('');
                $('.uniform-item').find('select').val('');
                $('.uniform-item').find('.uniform-quantity').val(1);
            }
        });
        
        // تحديث سعر قطعة الزي عند اختيارها
        $(document).on('change', '.uniform-item-select', function() {
            const price = $(this).find(':selected').data('price');
            $(this).closest('.uniform-item').find('.uniform-price').val(price);
        });
        
        // تفعيل/تعطيل خيارات التقسيط
        $('#is_installment').on('change', function() {
            if ($(this).is(':checked')) {
                $('#installment-options').removeClass('d-none');
            } else {
                $('#installment-options').addClass('d-none');
                $('#installment-amounts-container').addClass('d-none');
                $('#existing-installments-container').addClass('d-none');
                $('#custom_installments').prop('checked', false);
                $('#edit_existing_installments').prop('checked', false);
            }
        });
        
        // تفعيل/تعطيل تعديل الأقساط الموجودة
        $('#edit_existing_installments').on('change', function() {
            if ($(this).is(':checked')) {
                $('#existing-installments-container').removeClass('d-none');
                $('#custom_installments').prop('checked', false);
                $('#installment-amounts-container').addClass('d-none');
            } else {
                $('#existing-installments-container').addClass('d-none');
            }
        });
        
        // تفعيل/تعطيل تخصيص مبالغ الأقساط الجديدة
        $('#custom_installments').on('change', function() {
            if ($(this).is(':checked')) {
                $('#installment-amounts-container').removeClass('d-none');
                $('#edit_existing_installments').prop('checked', false);
                $('#existing-installments-container').addClass('d-none');
                createInstallmentFields();
            } else {
                $('#installment-amounts-container').addClass('d-none');
            }
        });
        
        // تحديث حقول الأقساط عند تغيير عدد الأقساط
        $('#installments_count').on('change', function() {
            if ($('#custom_installments').is(':checked')) {
                createInstallmentFields();
            }
        });
        
        // تحديث مبالغ الأقساط عند تغيير الدفعة المقدمة
        $('#down_payment').on('change', function() {
            updateInstallmentAmounts();
        });
        
        // تحديث إجمالي المصروفات عند تغيير أي حقل
        $('.fee-input').on('input', function() {
            calculateTotalFees();
        });
        
        // تحديث حالة المبلغ المخصص عند تغيير مبلغ القسط الموجود
        $(document).on('input', '.existing-installment-amount', function() {
            const originalAmount = parseFloat($(this).data('original'));
            const newAmount = parseFloat($(this).val() || 0);
            const reasonField = $(this).closest('.existing-installment').find('input[name="existing_installment_reasons[]"]');
            
            if (Math.abs(newAmount - originalAmount) > 0.01) {
                // المبلغ تغير - اجعل حقل السبب مطلوب
                reasonField.attr('required', true);
                reasonField.addClass('border-warning');
                if (!reasonField.val()) {
                    reasonField.attr('placeholder', 'مطلوب: سبب تغيير المبلغ');
                }
            } else {
                // المبلغ لم يتغير - اجعل حقل السبب اختياري
                reasonField.removeAttr('required');
                reasonField.removeClass('border-warning');
                reasonField.attr('placeholder', 'سبب تخصيص المبلغ (اختياري)');
            }
        });
        
        // حساب الإجمالي عند تحميل الصفحة
        calculateTotalFees();
    });
</script>
@endsection