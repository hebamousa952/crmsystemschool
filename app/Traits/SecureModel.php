<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

trait SecureModel
{
    /**
     * Override the fill method to add validation
     */
    public function fill(array $attributes)
    {
        // التحقق من وجود قواعد التحقق
        if (property_exists($this, 'rules') || isset(static::$rules)) {
            $this->validateAttributes($attributes);
        }

        return parent::fill($attributes);
    }

    /**
     * Validate attributes before mass assignment
     */
    protected function validateAttributes(array $attributes)
    {
        $rules = static::$rules ?? [];
        
        if (empty($rules)) {
            return;
        }

        // تصفية القواعد للحقول الموجودة فقط
        $filteredRules = array_intersect_key($rules, $attributes);
        
        if (empty($filteredRules)) {
            return;
        }

        $validator = Validator::make($attributes, $filteredRules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Safely create a new model instance with validation
     */
    public static function safeCreate(array $attributes)
    {
        $instance = new static();
        $instance->fill($attributes);
        $instance->save();
        
        return $instance;
    }

    /**
     * Safely update model with validation
     */
    public function safeUpdate(array $attributes)
    {
        $this->fill($attributes);
        $this->save();
        
        return $this;
    }

    /**
     * Get validation rules for the model
     */
    public static function getValidationRules()
    {
        return static::$rules ?? [];
    }

    /**
     * Get fillable attributes with their validation rules
     */
    public function getFillableWithRules()
    {
        $fillable = $this->getFillable();
        $rules = static::getValidationRules();
        
        $result = [];
        foreach ($fillable as $field) {
            $result[$field] = $rules[$field] ?? 'no validation';
        }
        
        return $result;
    }

    /**
     * Check if a field is safely fillable (has validation rules)
     */
    public function isSafelyFillable($field)
    {
        $rules = static::getValidationRules();
        return in_array($field, $this->getFillable()) && isset($rules[$field]);
    }

    /**
     * Get sensitive fields that should be handled carefully
     */
    public function getSensitiveFields()
    {
        return $this->hidden ?? [];
    }

    /**
     * Sanitize input data before processing
     */
    public static function sanitizeInput(array $data)
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // إزالة المسافات الزائدة والعلامات الخطيرة
                $sanitized[$key] = trim(strip_tags($value));
            } elseif (is_array($value)) {
                $sanitized[$key] = static::sanitizeInput($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }

    /**
     * Create model with sanitized input
     */
    public static function createSanitized(array $attributes)
    {
        $sanitized = static::sanitizeInput($attributes);
        return static::safeCreate($sanitized);
    }

    /**
     * Update model with sanitized input
     */
    public function updateSanitized(array $attributes)
    {
        $sanitized = static::sanitizeInput($attributes);
        return $this->safeUpdate($sanitized);
    }

    /**
     * Get model security summary
     */
    public function getSecuritySummary()
    {
        return [
            'fillable_count' => count($this->getFillable()),
            'guarded_count' => count($this->getGuarded()),
            'hidden_count' => count($this->getHidden()),
            'has_validation' => !empty(static::getValidationRules()),
            'validation_rules_count' => count(static::getValidationRules()),
            'sensitive_fields' => $this->getSensitiveFields()
        ];
    }
}
