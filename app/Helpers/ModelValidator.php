<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ModelValidator
{
    /**
     * التحقق من وجود حقل في الـ Model
     */
    public static function hasField(Model $model, string $field): bool
    {
        Log::info("=== FIELD VALIDATION ===");
        Log::info("Checking field: {$field} in model: " . get_class($model));

        $fillable = $model->getFillable();
        $hasInFillable = in_array($field, $fillable);

        Log::info("Field in fillable: " . ($hasInFillable ? 'YES' : 'NO'));
        Log::info("Fillable fields: " . json_encode($fillable));

        return $hasInFillable || $model->hasAttribute($field);
    }

    /**
     * التحقق من وجود علاقة في الـ Model
     */
    public static function hasRelation(Model $model, string $relation): bool
    {
        Log::info("=== RELATION VALIDATION ===");
        Log::info("Checking relation: {$relation} in model: " . get_class($model));

        $hasMethod = method_exists($model, $relation);
        Log::info("Relation exists: " . ($hasMethod ? 'YES' : 'NO'));

        return $hasMethod;
    }

    /**
     * التحقق من صحة البيانات قبل الحفظ
     */
    public static function validateData(Model $model, array $data): array
    {
        Log::info("=== DATA VALIDATION ===");
        Log::info("Validating data for model: " . get_class($model));
        Log::info("Input data: " . json_encode($data));

        $validData = [];
        $fillable = $model->getFillable();

        foreach ($data as $field => $value) {
            if (in_array($field, $fillable)) {
                $validData[$field] = $value;
                Log::info("Field {$field}: VALID");
            } else {
                Log::warning("Field {$field}: NOT IN FILLABLE - SKIPPED");
            }
        }

        Log::info("Valid data: " . json_encode($validData));
        return $validData;
    }

    /**
     * التحقق من جميع الحقول المطلوبة
     */
    public static function validateRequiredFields(array $data, array $requiredFields): bool
    {
        Log::info("=== REQUIRED FIELDS VALIDATION ===");
        Log::info("Required fields: " . json_encode($requiredFields));

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                Log::error("Required field missing: {$field}");
                return false;
            }
            Log::info("Required field {$field}: PRESENT");
        }

        Log::info("All required fields: VALID");
        return true;
    }
}
