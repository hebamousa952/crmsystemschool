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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();

            // العلاقات الأساسية
            $table->foreignId('student_fee_record_id')->constrained('student_fee_records')->onDelete('cascade')->comment('معرف سجل مصروفات الطالب');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade')->comment('معرف الطالب');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->onDelete('set null')->comment('معرف الفاتورة (اختياري)');
            $table->foreignId('online_payment_id')->nullable()->constrained('online_payments')->onDelete('set null')->comment('معرف الدفعة الإلكترونية (اختياري)');

            // معلومات الاسترداد
            $table->string('refund_number')->unique()->comment('رقم الاسترداد');
            $table->string('refund_series')->default('REF')->comment('سلسلة الاسترداد');
            $table->enum('refund_type', ['استرداد كامل', 'استرداد جزئي', 'استرداد رسوم', 'استرداد خصم', 'استرداد غرامة', 'استرداد مصروفات', 'استرداد أقساط'])->comment('نوع الاسترداد');
            $table->enum('refund_category', ['انسحاب طالب', 'إلغاء خدمة', 'خطأ في الدفع', 'تغيير خطة', 'قرار إداري', 'ظروف خاصة', 'أخرى'])->comment('فئة الاسترداد');

            // المبالغ
            $table->decimal('original_amount', 10, 2)->comment('المبلغ الأصلي المدفوع');
            $table->decimal('refund_amount', 10, 2)->comment('مبلغ الاسترداد');
            $table->decimal('refund_percentage', 5, 2)->default(0)->comment('نسبة الاسترداد');
            $table->decimal('processing_fee', 10, 2)->default(0)->comment('رسوم المعالجة');
            $table->decimal('penalty_amount', 10, 2)->default(0)->comment('مبلغ الغرامة المخصوم');
            $table->decimal('net_refund_amount', 10, 2)->comment('صافي مبلغ الاسترداد');

            // التواريخ
            $table->date('refund_date')->comment('تاريخ الاسترداد');
            $table->date('requested_date')->comment('تاريخ طلب الاسترداد');
            $table->date('approved_date')->nullable()->comment('تاريخ الموافقة');
            $table->date('processed_date')->nullable()->comment('تاريخ المعالجة');
            $table->date('completed_date')->nullable()->comment('تاريخ الإكمال');

            // معلومات الطلب
            $table->text('refund_reason')->comment('سبب الاسترداد');
            $table->text('refund_details')->nullable()->comment('تفاصيل الاسترداد');
            $table->json('supporting_documents')->nullable()->comment('المستندات المؤيدة (JSON)');
            $table->string('requested_by')->comment('طالب الاسترداد');
            $table->string('customer_name')->comment('اسم العميل');
            $table->string('customer_phone')->nullable()->comment('هاتف العميل');
            $table->string('customer_email')->nullable()->comment('بريد العميل الإلكتروني');

            // حالة الاسترداد
            $table->enum('status', ['مطلوب', 'قيد المراجعة', 'موافق عليه', 'مرفوض', 'قيد المعالجة', 'مكتمل', 'ملغي', 'معلق'])->default('مطلوب')->comment('حالة الاسترداد');
            $table->boolean('is_approved')->default(false)->comment('هل موافق عليه؟');
            $table->boolean('is_processed')->default(false)->comment('هل تم معالجته؟');
            $table->boolean('is_completed')->default(false)->comment('هل مكتمل؟');

            // معلومات الموافقة
            $table->boolean('requires_approval')->default(true)->comment('يتطلب موافقة؟');
            $table->string('approved_by')->nullable()->comment('معتمد بواسطة');
            $table->text('approval_notes')->nullable()->comment('ملاحظات الموافقة');
            $table->string('rejection_reason')->nullable()->comment('سبب الرفض');

            // معلومات المعالجة
            $table->enum('refund_method', ['نقدي', 'شيك', 'تحويل بنكي', 'بطاقة ائتمان', 'محفظة إلكترونية', 'فوري', 'أخرى'])->nullable()->comment('طريقة الاسترداد');
            $table->string('refund_reference')->nullable()->comment('مرجع الاسترداد');
            $table->string('bank_account')->nullable()->comment('الحساب البنكي');
            $table->string('bank_name')->nullable()->comment('اسم البنك');
            $table->text('refund_instructions')->nullable()->comment('تعليمات الاسترداد');

            // معلومات المعالج
            $table->string('processed_by')->nullable()->comment('معالج بواسطة');
            $table->text('processing_notes')->nullable()->comment('ملاحظات المعالجة');
            $table->json('processing_details')->nullable()->comment('تفاصيل المعالجة (JSON)');

            // الرسوم والخصومات
            $table->json('fee_breakdown')->nullable()->comment('تفصيل الرسوم (JSON)');
            $table->decimal('admin_fee', 10, 2)->default(0)->comment('رسوم إدارية');
            $table->decimal('cancellation_fee', 10, 2)->default(0)->comment('رسوم الإلغاء');
            $table->boolean('is_fee_waived')->default(false)->comment('هل تم إعفاء الرسوم؟');
            $table->string('fee_waiver_reason')->nullable()->comment('سبب إعفاء الرسوم');

            // معلومات التدقيق
            $table->boolean('is_audited')->default(false)->comment('هل تم تدقيقه؟');
            $table->string('audited_by')->nullable()->comment('مدقق بواسطة');
            $table->timestamp('audited_at')->nullable()->comment('وقت التدقيق');
            $table->text('audit_notes')->nullable()->comment('ملاحظات التدقيق');

            // معلومات إضافية
            $table->text('notes')->nullable()->comment('ملاحظات عامة');
            $table->json('metadata')->nullable()->comment('بيانات إضافية (JSON)');
            $table->string('currency', 3)->default('EGP')->comment('العملة');
            $table->decimal('exchange_rate', 8, 4)->default(1)->comment('سعر الصرف');

            // معلومات الإنشاء والتحديث
            $table->string('created_by')->nullable()->comment('منشئ السجل');
            $table->string('updated_by')->nullable()->comment('آخر محدث للسجل');

            $table->timestamps();

            // الفهارس
            $table->index(['student_fee_record_id', 'status']);
            $table->index(['student_id', 'refund_type']);
            $table->index(['invoice_id', 'status']);
            $table->index(['online_payment_id', 'status']);
            $table->index('refund_number');
            $table->index('refund_type');
            $table->index('refund_category');
            $table->index('status');
            $table->index('refund_date');
            $table->index('requested_date');
            $table->index('is_approved');
            $table->index('is_processed');
            $table->index('is_completed');
            $table->index('requires_approval');
            $table->index('is_audited');

            // فهرس مركب للبحث السريع
            $table->index(['student_id', 'status', 'refund_type'], 'student_status_type_index');
            $table->index(['refund_date', 'status'], 'date_status_index');
            $table->index(['requested_date', 'is_approved'], 'requested_approved_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('refunds');
    }
};
