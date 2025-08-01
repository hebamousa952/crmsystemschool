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
        Schema::create('student_fee_records', function (Blueprint $table) {
            $table->id();

            // العلاقات الأساسية
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade')->comment('معرف الطالب');
            $table->foreignId('fee_plan_id')->constrained('fees_plans')->onDelete('cascade')->comment('معرف خطة المصروفات');

            // معلومات السجل
            $table->string('academic_year', 20)->comment('العام الدراسي');
            $table->enum('semester', ['الفصل الأول', 'الفصل الثاني', 'العام كامل'])->default('العام كامل')->comment('الفصل الدراسي');

            // المصروفات المخصصة للطالب (قد تختلف عن الخطة العامة)
            $table->decimal('basic_fees', 10, 2)->default(0)->comment('قيمة المصروفات الأساسية');
            $table->decimal('registration_fees', 10, 2)->default(0)->comment('رسوم تسجيل أو تقديم');
            $table->decimal('uniform_fees', 10, 2)->default(0)->comment('رسوم الزي المدرسي');
            $table->integer('uniform_pieces')->default(0)->comment('عدد قطع الزي');
            $table->decimal('books_fees', 10, 2)->default(0)->comment('رسوم الكتب الدراسية');
            $table->decimal('activities_fees', 10, 2)->default(0)->comment('رسوم الأنشطة المدرسية');
            $table->decimal('bus_fees', 10, 2)->default(0)->comment('رسوم الباص المدرسي');
            $table->decimal('exam_fees', 10, 2)->default(0)->comment('رسوم امتحانات');
            $table->decimal('platform_fees', 10, 2)->default(0)->comment('رسوم إلكترونية / منصة تعليمية');
            $table->decimal('insurance_fees', 10, 2)->default(0)->comment('رسوم تأمين');
            $table->decimal('service_fees', 10, 2)->default(0)->comment('رسوم الخدمة / الإدارة');
            $table->decimal('other_fees', 10, 2)->default(0)->comment('رسوم إضافية أخرى');
            $table->text('other_fees_description')->nullable()->comment('وصف الرسوم الإضافية');

            // الإجماليات المحسوبة
            $table->decimal('total_fees', 10, 2)->default(0)->comment('إجمالي المصروفات');
            $table->decimal('total_paid', 10, 2)->default(0)->comment('إجمالي المدفوع');
            $table->decimal('remaining_amount', 10, 2)->default(0)->comment('المبلغ المتبقي');

            // معلومات التقسيط
            $table->boolean('is_installment')->default(false)->comment('هل تم تقسيط المصروفات؟');
            $table->integer('installments_count')->default(1)->comment('عدد الأقساط');
            $table->decimal('down_payment', 10, 2)->default(0)->comment('الدفعة المقدمة');

            // حالة السداد
            $table->enum('payment_status', ['غير مدفوع', 'مدفوع جزئياً', 'مدفوع كاملاً', 'متأخر', 'معفى'])->default('غير مدفوع')->comment('حالة السداد');
            $table->date('due_date')->nullable()->comment('تاريخ الاستحقاق');
            $table->date('last_payment_date')->nullable()->comment('تاريخ آخر دفعة');

            // ملاحظات ومعلومات إضافية
            $table->text('notes')->nullable()->comment('ملاحظات على السجل');
            $table->boolean('is_active')->default(true)->comment('هل السجل نشط؟');

            // معلومات الإنشاء والتحديث
            $table->string('created_by')->nullable()->comment('منشئ السجل');
            $table->string('updated_by')->nullable()->comment('آخر محدث للسجل');

            $table->timestamps();

            // الفهارس
            $table->index(['student_id', 'academic_year']);
            $table->index(['fee_plan_id', 'academic_year']);
            $table->index('payment_status');
            $table->index('due_date');
            $table->index('is_active');

            // فهرس مركب للبحث السريع
            $table->index(['student_id', 'academic_year', 'semester'], 'student_academic_semester_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_fee_records');
    }
};
