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
            // بداية العملية مع Logging مفصل
            \Illuminate\Support\Facades\Log::info("=== [ADD_CUSTOM_AMOUNT_REASON_TO_INSTALLMENTS] STARTED ===");
            \Illuminate\Support\Facades\Log::info("Timestamp: " . now());
            
            // التحقق من وجود الجدول
            if (!Schema::hasTable('installments')) {
                \Illuminate\Support\Facades\Log::error("Table 'installments' does not exist");
                throw new Exception("Table 'installments' not found");
            }
            
            // التحقق من عدم وجود الحقل مسبقاً
            if (Schema::hasColumn('installments', 'custom_amount_reason')) {
                \Illuminate\Support\Facades\Log::warning("Column 'custom_amount_reason' already exists, skipping...");
                return;
            }
            
            \Illuminate\Support\Facades\Log::info("Adding custom_amount_reason column to installments table");
            
            Schema::table('installments', function (Blueprint $table) {
                $table->text('custom_amount_reason')
                      ->nullable()
                      ->after('is_custom_amount')
                      ->comment('سبب تخصيص مبلغ القسط');
            });
            
            \Illuminate\Support\Facades\Log::info("Successfully added custom_amount_reason column to installments table");
            \Illuminate\Support\Facades\Log::info("=== [ADD_CUSTOM_AMOUNT_REASON_TO_INSTALLMENTS] COMPLETED ===");
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("=== [ADD_CUSTOM_AMOUNT_REASON_TO_INSTALLMENTS] FAILED ===");
            \Illuminate\Support\Facades\Log::error("Error: " . $e->getMessage());
            \Illuminate\Support\Facades\Log::error("Stack trace: " . $e->getTraceAsString());
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
        try {
            \Illuminate\Support\Facades\Log::info("=== [ROLLBACK_CUSTOM_AMOUNT_REASON_FROM_INSTALLMENTS] STARTED ===");
            
            // التحقق من وجود الحقل قبل الحذف
            if (!Schema::hasColumn('installments', 'custom_amount_reason')) {
                \Illuminate\Support\Facades\Log::warning("Column 'custom_amount_reason' does not exist, nothing to rollback");
                return;
            }
            
            \Illuminate\Support\Facades\Log::info("Dropping custom_amount_reason column from installments table");
            
            Schema::table('installments', function (Blueprint $table) {
                $table->dropColumn('custom_amount_reason');
            });
            
            \Illuminate\Support\Facades\Log::info("Successfully dropped custom_amount_reason column from installments table");
            \Illuminate\Support\Facades\Log::info("=== [ROLLBACK_CUSTOM_AMOUNT_REASON_FROM_INSTALLMENTS] COMPLETED ===");
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("=== [ROLLBACK_CUSTOM_AMOUNT_REASON_FROM_INSTALLMENTS] FAILED ===");
            \Illuminate\Support\Facades\Log::error("Error: " . $e->getMessage());
            throw $e;
        }
    }
};
