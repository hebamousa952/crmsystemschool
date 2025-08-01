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
        Schema::create('arrears', function (Blueprint $table) {
            $table->id();

            // العلاقات الأساسية
            $table->foreignId('student_fee_record_id')->constrained('student_fee_records')->onDelete('cascade')->comment('معرف سجل مصروفات الطالب');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade')->comment('معرف الطالب');
            $table->foreignId('installment_id')->nullable()->constrained('installments')->onDelete('cascade')->comment('معرف القسط المتأخر (اختياري)');

            // معلومات المتأخرات
            $table->string('arrear_type')->default('تأخير سداد')->comment('نوع المتأخرات');
            $table->enum('arrear_category', ['تأخير أقساط', 'تأخير مصروفات', 'غرامة إدارية', 'رسوم تأخير', 'غرامة انضباط', 'رسوم إضافية'])->comment('فئة المتأخرات');
            $table->string('arrear_description')->comment('وصف المتأخرات');

            // المبالغ والحسابات
            $table->decimal('original_amount', 10, 2)->comment('المبلغ الأصلي المتأخر');
            $table->decimal('penalty_rate', 5, 2)->default(0)->comment('معدل الغرامة (%)');
            $table->decimal('penalty_amount', 10, 2)->default(0)->comment('مبلغ الغرامة');
            $table->decimal('additional_fees', 10, 2)->default(0)->comment('رسوم إضافية');
            $table->decimal('total_arrear_amount', 10, 2)->default(0)->comment('إجمالي مبلغ المتأخرات');
            $table->decimal('paid_amount', 10, 2)->default(0)->comment('المبلغ المدفوع من المتأخرات');
            $table->decimal('remaining_amount', 10, 2)->default(0)->comment('المبلغ المتبقي من المتأخرات');

            // التواريخ المهمة
            $table->date('original_due_date')->comment('تاريخ الاستحقاق الأصلي');
            $table->date('arrear_start_date')->comment('تاريخ بداية المتأخرات');
            $table->date('grace_period_end')->nullable()->comment('نهاية فترة السماح');
            $table->integer('days_overdue')->default(0)->comment('عدد أيام التأخير');
            $table->date('last_calculation_date')->nullable()->comment('تاريخ آخر حساب للغرامة');

            // حالة المتأخرات
            $table->enum('status', ['نشط', 'مدفوع جزئياً', 'مدفوع كاملاً', 'معفى', 'مؤجل', 'ملغي'])->default('نشط')->comment('حالة المتأخرات');
            $table->boolean('is_active')->default(true)->comment('هل المتأخرات نشطة؟');
            $table->boolean('auto_calculate')->default(true)->comment('حساب الغرامة تلقائياً؟');
            $table->boolean('compound_interest')->default(false)->comment('فائدة مركبة؟');

            // إعدادات الحساب
            $table->enum('calculation_method', ['يومي', 'أسبوعي', 'شهري', 'ثابت'])->default('شهري')->comment('طريقة حساب الغرامة');
            $table->integer('calculation_frequency')->default(30)->comment('تكرار الحساب (بالأيام)');
            $table->decimal('max_penalty_amount', 10, 2)->nullable()->comment('الحد الأقصى للغرامة');
            $table->decimal('min_penalty_amount', 10, 2)->nullable()->comment('الحد الأدنى للغرامة');

            // معلومات الإعفاء والتأجيل
            $table->boolean('is_exempted')->default(false)->comment('هل معفى من الغرامة؟');
            $table->string('exemption_reason')->nullable()->comment('سبب الإعفاء');
            $table->string('exempted_by')->nullable()->comment('الشخص الذي أعفى');
            $table->date('exemption_date')->nullable()->comment('تاريخ الإعفاء');
            $table->boolean('is_deferred')->default(false)->comment('هل مؤجل؟');
            $table->date('deferred_until')->nullable()->comment('مؤجل حتى تاريخ');
            $table->string('deferment_reason')->nullable()->comment('سبب التأجيل');

            // معلومات الدفع
            $table->date('first_payment_date')->nullable()->comment('تاريخ أول دفعة');
            $table->date('last_payment_date')->nullable()->comment('تاريخ آخر دفعة');
            $table->string('payment_method')->nullable()->comment('طريقة الدفع');
            $table->text('payment_notes')->nullable()->comment('ملاحظات الدفع');

            // الإشعارات والتذكيرات
            $table->integer('notification_count')->default(0)->comment('عدد الإشعارات المرسلة');
            $table->date('last_notification_date')->nullable()->comment('تاريخ آخر إشعار');
            $table->date('next_notification_date')->nullable()->comment('تاريخ الإشعار التالي');
            $table->boolean('legal_action_threatened')->default(false)->comment('هل تم التهديد بإجراء قانوني؟');
            $table->date('legal_action_date')->nullable()->comment('تاريخ الإجراء القانوني');

            // معلومات إضافية
            $table->text('notes')->nullable()->comment('ملاحظات على المتأخرات');
            $table->string('reference_number')->nullable()->comment('رقم مرجعي');
            $table->json('calculation_history')->nullable()->comment('تاريخ الحسابات (JSON)');

            // معلومات الإنشاء والتحديث
            $table->string('created_by')->nullable()->comment('منشئ السجل');
            $table->string('updated_by')->nullable()->comment('آخر محدث للسجل');

            $table->timestamps();

            // الفهارس
            $table->index(['student_fee_record_id', 'status']);
            $table->index(['student_id', 'arrear_category']);
            $table->index(['installment_id', 'status']);
            $table->index('status');
            $table->index('arrear_category');
            $table->index('original_due_date');
            $table->index('arrear_start_date');
            $table->index('is_active');
            $table->index('is_exempted');
            $table->index('is_deferred');
            $table->index('auto_calculate');

            // فهرس مركب للبحث السريع
            $table->index(['student_id', 'status', 'is_active'], 'student_arrear_status_index');
            $table->index(['original_due_date', 'status'], 'due_date_status_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('arrears');
    }
};
