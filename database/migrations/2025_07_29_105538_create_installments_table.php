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
        Schema::create('installments', function (Blueprint $table) {
            $table->id();

            // العلاقات الأساسية
            $table->foreignId('student_fee_record_id')->constrained('student_fee_records')->onDelete('cascade')->comment('معرف سجل مصروفات الطالب');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade')->comment('معرف الطالب');

            // معلومات القسط
            $table->integer('installment_number')->comment('رقم القسط');
            $table->string('installment_name')->nullable()->comment('اسم القسط (اختياري)');
            $table->decimal('amount', 10, 2)->comment('قيمة القسط');
            $table->decimal('paid_amount', 10, 2)->default(0)->comment('المبلغ المدفوع من القسط');
            $table->decimal('remaining_amount', 10, 2)->default(0)->comment('المبلغ المتبقي من القسط');

            // تواريخ القسط
            $table->date('due_date')->comment('تاريخ استحقاق القسط');
            $table->date('paid_date')->nullable()->comment('تاريخ سداد القسط');
            $table->date('grace_period_end')->nullable()->comment('نهاية فترة السماح');

            // حالة القسط
            $table->enum('status', ['متبقي', 'مدفوع جزئياً', 'مدفوع كاملاً', 'متأخر', 'ملغي'])->default('متبقي')->comment('حالة القسط');
            $table->boolean('is_overdue')->default(false)->comment('هل القسط متأخر؟');
            $table->integer('overdue_days')->default(0)->comment('عدد أيام التأخير');

            // رسوم التأخير
            $table->decimal('late_fee', 10, 2)->default(0)->comment('رسوم التأخير');
            $table->decimal('late_fee_rate', 5, 2)->default(0)->comment('معدل رسوم التأخير (%)');
            $table->boolean('late_fee_applied')->default(false)->comment('هل تم تطبيق رسوم التأخير؟');

            // معلومات الدفع
            $table->string('payment_method')->nullable()->comment('طريقة الدفع');
            $table->string('payment_reference')->nullable()->comment('مرجع الدفعة');
            $table->text('payment_notes')->nullable()->comment('ملاحظات الدفع');

            // معلومات إضافية
            $table->text('notes')->nullable()->comment('ملاحظات على القسط');
            $table->boolean('is_active')->default(true)->comment('هل القسط نشط؟');
            $table->boolean('auto_calculate_late_fee')->default(true)->comment('حساب رسوم التأخير تلقائياً؟');

            // معلومات الإنشاء والتحديث
            $table->string('created_by')->nullable()->comment('منشئ القسط');
            $table->string('updated_by')->nullable()->comment('آخر محدث للقسط');
            $table->string('paid_by')->nullable()->comment('الشخص الذي دفع القسط');

            $table->timestamps();

            // الفهارس
            $table->index(['student_fee_record_id', 'installment_number']);
            $table->index(['student_id', 'due_date']);
            $table->index('status');
            $table->index('due_date');
            $table->index('is_overdue');
            $table->index('is_active');

            // فهرس مركب للبحث السريع
            $table->index(['student_id', 'status', 'due_date'], 'student_status_due_index');

            // قيد فريد لمنع تكرار رقم القسط لنفس السجل
            $table->unique(['student_fee_record_id', 'installment_number'], 'unique_installment_per_record');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('installments');
    }
};
