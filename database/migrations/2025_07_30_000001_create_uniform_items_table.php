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
            \Illuminate\Support\Facades\Log::info("=== [CREATE_UNIFORM_ITEMS_TABLE] STARTED ===");
            
            Schema::create('uniform_items', function (Blueprint $table) {
                $table->id();
                $table->string('name')->comment('اسم قطعة الزي');
                $table->enum('type', ['صيفي', 'شتوي', 'موحد'])->comment('نوع الزي');
                $table->enum('gender', ['ذكر', 'أنثى', 'الجميع'])->default('الجميع')->comment('الجنس المناسب');
                $table->decimal('price', 10, 2)->default(0)->comment('سعر القطعة');
                $table->string('grade_level')->nullable()->comment('المرحلة الدراسية');
                $table->string('grade', 50)->nullable()->comment('الصف الدراسي المحدد');
                $table->boolean('is_active')->default(true)->comment('هل القطعة متاحة؟');
                $table->text('description')->nullable()->comment('وصف القطعة');
                $table->string('created_by')->nullable()->comment('منشئ القطعة');
                $table->string('updated_by')->nullable()->comment('آخر محدث للقطعة');
                $table->timestamps();
                
                // الفهارس
                $table->index(['type', 'grade_level']);
                $table->index('is_active');
            });
            
            \Illuminate\Support\Facades\Log::info("Uniform items table created successfully");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to create uniform items table: " . $e->getMessage());
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
        Schema::dropIfExists('uniform_items');
    }
};