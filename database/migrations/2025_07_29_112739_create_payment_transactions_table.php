<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            // العلاقات الأساسية
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade')->comment('معرف الطالب');
            $table->foreignId('guardian_id')->nullable()->constrained('guardian_accounts')->onDelete('set null')->comment('معرف ولي الأمر');
            $table->foreignId('student_fee_record_id')->nullable()->constrained('student_fee_records')->onDelete('cascade')->comment('معرف سجل المصروفات');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null')->comment('معرف الفاتورة');
            $table->foreignId('installment_id')->nullable()->constrained('installments')->onDelete('set null')->comment('معرف القسط');
            $table->foreignId('online_payment_id')->nullable()->constrained('online_payments')->onDelete('set null')->comment('معرف الدفعة الإلكترونية');

            // معلومات المعاملة الأساسية
            $table->string('transaction_number')->unique()->comment('رقم المعاملة');
            $table->string('reference_number')->nullable()->comment('الرقم المرجعي');
            $table->string('external_transaction_id')->nullable()->comment('معرف المعاملة الخارجي');
            $table->enum('transaction_type', ['دفع', 'استرداد', 'تحويل', 'خصم', 'إضافة رصيد', 'سحب رصيد', 'غرامة', 'مكافأة', 'تعديل'])->comment('نوع المعاملة');
            $table->enum('category', ['رسوم دراسية', 'رسوم إضافية', 'غرامات', 'خصومات', 'مكافآت', 'تحويلات', 'أخرى'])->comment('فئة المعاملة');

            // المبالغ والعملة
            $table->decimal('amount', 12, 2)->comment('مبلغ المعاملة');
            $table->decimal('original_amount', 12, 2)->nullable()->comment('المبلغ الأصلي (قبل التحويل)');
            $table->string('currency', 3)->default('EGP')->comment('العملة');
            $table->decimal('exchange_rate', 8, 4)->default(1)->comment('سعر الصرف');
            $table->decimal('fees', 10, 2)->default(0)->comment('رسوم المعاملة');
            $table->decimal('net_amount', 12, 2)->comment('صافي المبلغ');

            // طريقة الدفع
            $table->enum('payment_method', ['نقدي', 'شيك', 'تحويل بنكي', 'بطاقة ائتمان', 'بطاقة خصم', 'محفظة إلكترونية', 'فوري', 'فودافون كاش', 'أورانج موني', 'اتصالات كاش', 'أخرى'])->comment('طريقة الدفع');
            $table->string('payment_gateway')->nullable()->comment('بوابة الدفع');
            $table->string('gateway_transaction_id')->nullable()->comment('معرف المعاملة في البوابة');
            $table->json('gateway_response')->nullable()->comment('استجابة البوابة (JSON)');

            // معلومات البطاقة/الحساب (مشفرة)
            $table->string('card_last_four')->nullable()->comment('آخر 4 أرقام من البطاقة');
            $table->string('card_type')->nullable()->comment('نوع البطاقة');
            $table->string('bank_name')->nullable()->comment('اسم البنك');
            $table->string('account_number')->nullable()->comment('رقم الحساب (مشفر)');
            $table->string('check_number')->nullable()->comment('رقم الشيك');

            // حالة المعاملة
            $table->enum('status', ['معلق', 'قيد المعالجة', 'مكتمل', 'فشل', 'ملغي', 'مرفوض', 'منتهي الصلاحية', 'قيد المراجعة'])->default('معلق')->comment('حالة المعاملة');
            $table->enum('payment_status', ['غير مدفوع', 'مدفوع جزئياً', 'مدفوع بالكامل', 'مسترد', 'مسترد جزئياً'])->default('غير مدفوع')->comment('حالة الدفع');
            $table->boolean('is_verified')->default(false)->comment('هل تم التحقق؟');
            $table->boolean('is_reconciled')->default(false)->comment('هل تم التسوية؟');

            // التواريخ المهمة
            $table->timestamp('transaction_date')->comment('تاريخ المعاملة');
            $table->timestamp('processed_at')->nullable()->comment('وقت المعالجة');
            $table->timestamp('completed_at')->nullable()->comment('وقت الإكمال');
            $table->timestamp('verified_at')->nullable()->comment('وقت التحقق');
            $table->timestamp('reconciled_at')->nullable()->comment('وقت التسوية');
            $table->date('value_date')->nullable()->comment('تاريخ القيمة');

            // معلومات الخطأ والفشل
            $table->text('failure_reason')->nullable()->comment('سبب الفشل');
            $table->string('error_code')->nullable()->comment('كود الخطأ');
            $table->text('error_message')->nullable()->comment('رسالة الخطأ');
            $table->integer('retry_count')->default(0)->comment('عدد محاولات الإعادة');
            $table->timestamp('last_retry_at')->nullable()->comment('وقت آخر محاولة');

            // معلومات المستخدم والجلسة
            $table->string('processed_by')->nullable()->comment('معالج بواسطة');
            $table->string('verified_by')->nullable()->comment('محقق بواسطة');
            $table->string('ip_address')->nullable()->comment('عنوان IP');
            $table->string('user_agent')->nullable()->comment('معلومات المتصفح');
            $table->string('session_id')->nullable()->comment('معرف الجلسة');

            // معلومات الأمان
            $table->string('security_hash')->nullable()->comment('هاش الأمان');
            $table->boolean('is_suspicious')->default(false)->comment('هل مشبوه؟');
            $table->text('security_notes')->nullable()->comment('ملاحظات أمنية');
            $table->json('fraud_check_result')->nullable()->comment('نتيجة فحص الاحتيال (JSON)');

            // معلومات التسوية والمحاسبة
            $table->string('batch_id')->nullable()->comment('معرف الدفعة');
            $table->date('settlement_date')->nullable()->comment('تاريخ التسوية');
            $table->string('settlement_reference')->nullable()->comment('مرجع التسوية');
            $table->decimal('settlement_amount', 12, 2)->nullable()->comment('مبلغ التسوية');
            $table->string('merchant_id')->nullable()->comment('معرف التاجر');

            // معلومات الاسترداد
            $table->boolean('is_refundable')->default(true)->comment('قابل للاسترداد؟');
            $table->decimal('refunded_amount', 12, 2)->default(0)->comment('المبلغ المسترد');
            $table->integer('refund_count')->default(0)->comment('عدد مرات الاسترداد');
            $table->timestamp('last_refund_at')->nullable()->comment('وقت آخر استرداد');

            // معلومات التقسيط والدفع المؤجل
            $table->boolean('is_installment')->default(false)->comment('هل دفعة تقسيط؟');
            $table->integer('installment_number')->nullable()->comment('رقم القسط');
            $table->integer('total_installments')->nullable()->comment('إجمالي الأقساط');
            $table->date('due_date')->nullable()->comment('تاريخ الاستحقاق');
            $table->integer('days_overdue')->default(0)->comment('عدد أيام التأخير');

            // معلومات الخصم والعمولة
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('مبلغ الخصم');
            $table->decimal('discount_percentage', 5, 2)->default(0)->comment('نسبة الخصم');
            $table->decimal('commission_amount', 10, 2)->default(0)->comment('مبلغ العمولة');
            $table->decimal('commission_percentage', 5, 2)->default(0)->comment('نسبة العمولة');

            // معلومات الضريبة
            $table->decimal('tax_amount', 10, 2)->default(0)->comment('مبلغ الضريبة');
            $table->decimal('tax_percentage', 5, 2)->default(0)->comment('نسبة الضريبة');
            $table->string('tax_id')->nullable()->comment('معرف الضريبة');

            // معلومات إضافية
            $table->text('description')->nullable()->comment('وصف المعاملة');
            $table->text('notes')->nullable()->comment('ملاحظات');
            $table->json('metadata')->nullable()->comment('بيانات إضافية (JSON)');
            $table->json('custom_fields')->nullable()->comment('حقول مخصصة (JSON)');

            // معلومات التتبع والتحليل
            $table->string('source')->default('manual')->comment('مصدر المعاملة');
            $table->string('channel')->nullable()->comment('قناة المعاملة');
            $table->string('campaign_id')->nullable()->comment('معرف الحملة');
            $table->json('analytics_data')->nullable()->comment('بيانات التحليل (JSON)');

            // معلومات الموافقة والامتثال
            $table->boolean('requires_approval')->default(false)->comment('يتطلب موافقة؟');
            $table->string('approved_by')->nullable()->comment('موافق عليه بواسطة');
            $table->timestamp('approved_at')->nullable()->comment('وقت الموافقة');
            $table->text('approval_notes')->nullable()->comment('ملاحظات الموافقة');

            // معلومات الإنشاء والتحديث
            $table->string('created_by')->nullable()->comment('منشئ المعاملة');
            $table->string('updated_by')->nullable()->comment('آخر محدث للمعاملة');

            $table->timestamps();

            // الفهارس الأساسية
            $table->index('student_id');
            $table->index('guardian_id');
            $table->index('student_fee_record_id');
            $table->index('invoice_id');
            $table->index('installment_id');
            $table->index('online_payment_id');
            $table->index('transaction_number');
            $table->index('reference_number');
            $table->index('external_transaction_id');
            $table->index('transaction_type');
            $table->index('category');
            $table->index('payment_method');
            $table->index('payment_gateway');
            $table->index('gateway_transaction_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index('is_verified');
            $table->index('is_reconciled');
            $table->index('transaction_date');
            $table->index('processed_at');
            $table->index('completed_at');
            $table->index('batch_id');
            $table->index('settlement_date');
            $table->index('is_refundable');
            $table->index('is_installment');
            $table->index('due_date');
            $table->index('is_suspicious');
            $table->index('requires_approval');
            $table->index('source');
            $table->index('channel');

            // فهارس مركبة للبحث السريع
            $table->index(['student_id', 'transaction_type', 'status'], 'student_type_status_index');
            $table->index(['transaction_date', 'payment_method', 'status'], 'date_method_status_index');
            $table->index(['payment_gateway', 'gateway_transaction_id'], 'gateway_transaction_index');
            $table->index(['status', 'is_verified', 'is_reconciled'], 'status_verified_reconciled_index');
            $table->index(['settlement_date', 'batch_id'], 'settlement_batch_index');
            $table->index(['due_date', 'days_overdue', 'status'], 'due_overdue_status_index');
            $table->index(['is_suspicious', 'requires_approval'], 'suspicious_approval_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_transactions');
    }
};
