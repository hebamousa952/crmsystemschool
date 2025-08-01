<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            Log::info("=== [FIX_ACTIVITY_LOGS_TABLE] MIGRATION STARTED ===");
            
            if (!Schema::hasTable('activity_logs')) {
                Log::error("Table 'activity_logs' does not exist");
                throw new Exception("Table 'activity_logs' not found");
            }
            
            Schema::table('activity_logs', function (Blueprint $table) {
                // إضافة الحقول المفقودة
                if (!Schema::hasColumn('activity_logs', 'is_sensitive')) {
                    $table->boolean('is_sensitive')->default(false)->after('severity');
                    Log::info("Added 'is_sensitive' column");
                }
                
                if (!Schema::hasColumn('activity_logs', 'requires_review')) {
                    $table->boolean('requires_review')->default(false)->after('is_sensitive');
                    Log::info("Added 'requires_review' column");
                }
            });

            Log::info("=== [FIX_ACTIVITY_LOGS_TABLE] MIGRATION COMPLETED SUCCESSFULLY ===");

        } catch (Exception $e) {
            Log::error("=== [FIX_ACTIVITY_LOGS_TABLE] MIGRATION FAILED ===");
            Log::error("Error message: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Log::info("=== [FIX_ACTIVITY_LOGS_TABLE] ROLLBACK STARTED ===");
            
            Schema::table('activity_logs', function (Blueprint $table) {
                if (Schema::hasColumn('activity_logs', 'is_sensitive')) {
                    $table->dropColumn('is_sensitive');
                }
                
                if (Schema::hasColumn('activity_logs', 'requires_review')) {
                    $table->dropColumn('requires_review');
                }
            });
            
            Log::info("=== [FIX_ACTIVITY_LOGS_TABLE] ROLLBACK COMPLETED ===");
            
        } catch (Exception $e) {
            Log::error("=== [FIX_ACTIVITY_LOGS_TABLE] ROLLBACK FAILED ===");
            Log::error("Error message: " . $e->getMessage());
            throw $e;
        }
    }
};
