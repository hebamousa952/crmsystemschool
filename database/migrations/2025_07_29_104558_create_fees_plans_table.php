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
        Schema::create('fees_plans', function (Blueprint $table) {
            $table->id();

            // معلومات الخطة الأساسية
            $table->string('plan_name')->comment('اسم خطة المصروفات');
            $table->enum('fee_type', ['سنوية', 'فصلية', 'شهرية', 'حسب البرنامج'])->comment('نوع المصروفات');
            $table->enum('program_type', ['وطني', 'لغات', 'دولي', 'حضانة', 'نشاط صيفي', 'دعم إضافي'])->comment('البرنامج الدراسي');
            $table->string('academic_year', 20)->comment('العام الدراسي');
            $table->enum('grade_level', ['primary', 'preparatory'])->comment('المرحلة الدراسية');
            $table->string('grade', 50)->nullable()->comment('الصف الدراسي المحدد');

            // المصروفات التفصيلية
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

            // حالة الخطة
            $table->boolean('is_active')->default(true)->comment('هل الخطة نشطة؟');
            $table->text('notes')->nullable()->comment('ملاحظات على الخطة');

            // معلومات الإنشاء والتحديث
            $table->string('created_by')->nullable()->comment('منشئ الخطة');
            $table->string('updated_by')->nullable()->comment('آخر محدث للخطة');

            $table->timestamps();

            // الفهارس
            $table->index(['fee_type', 'program_type']);
            $table->index(['academic_year', 'grade_level']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fees_plans');
    }
};
