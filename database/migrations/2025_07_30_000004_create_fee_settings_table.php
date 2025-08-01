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
        try {
            // بداية العملية
            \Illuminate\Support\Facades\Log::info("=== [CREATE_FEE_SETTINGS_TABLE] STARTED ===");
            
            Schema::create('fee_settings', function (Blueprint $table) {
                $table->id();
                $table->string('academic_year', 20)->comment('العام الدراسي');
                $table->enum('grade_level', ['primary', 'preparatory'])->comment('المرحلة الدراسية');
                $table->string('grade', 50)->nullable()->comment('الصف الدراسي المحدد');
                $table->enum('program_type', ['وطني', 'لغات', 'دولي', 'حضانة', 'نشاط صيفي', 'دعم إضافي'])->comment('البرنامج الدراسي');
                
                // الرسوم الافتراضية
                $table->decimal('basic_fees', 10, 2)->default(0)->comment('الرسوم الأساسية');
                $table->decimal('registration_fees', 10, 2)->default(0)->comment('رسوم التسجيل');
                $table->decimal('activities_fees', 10, 2)->default(0)->comment('رسوم الأنشطة');
                $table->decimal('bus_fees', 10, 2)->default(0)->comment('رسوم النقل');
                $table->decimal('books_fees', 10, 2)->default(0)->comment('رسوم الكتب');
                $table->decimal('exam_fees', 10, 2)->default(0)->comment('رسوم امتحانات');
                $table->decimal('platform_fees', 10, 2)->default(0)->comment('رسوم إلكترونية / منصة تعليمية');
                $table->decimal('insurance_fees', 10, 2)->default(0)->comment('رسوم تأمين');
                $table->decimal('service_fees', 10, 2)->default(0)->comment('رسوم الخدمة / الإدارة');
                $table->decimal('other_fees', 10, 2)->default(0)->comment('رسوم إضافية أخرى');
                $table->text('other_fees_description')->nullable()->comment('وصف الرسوم الإضافية');
                
                // إعدادات الخصم الافتراضية
                $table->json('default_discounts')->nullable()->comment('الخصومات الافتراضية');
                
                // إعدادات التقسيط الافتراضية
                $table->integer('max_installments')->default(10)->comment('الحد الأقصى للأقساط');
                $table->json('default_installment_plans')->nullable()->comment('خطط التقسيط الافتراضية');
                
                // إعدادات الزي المدرسي
                $table->boolean('has_uniform')->default(false)->comment('هل يوجد زي مدرسي؟');
                $table->json('default_uniform_items')->nullable()->comment('قطع الزي الافتراضية');
                
                $table->boolean('is_active')->default(true)->comment('هل الإعدادات نشطة؟');
                $table->text('notes')->nullable()->comment('ملاحظات على الإعدادات');
                $table->string('created_by')->nullable()->comment('منشئ الإعدادات');
                $table->string('updated_by')->nullable()->comment('آخر محدث للإعدادات');
                $table->timestamps();
                
                // فهرس مركب
                $table->unique(['academic_year', 'grade_level', 'grade', 'program_type'], 'unique_fee_setting');
                $table->index('is_active');
            });
            
            \Illuminate\Support\Facades\Log::info("Fee settings table created successfully");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to create fee settings table: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fee_settings');
    }
};