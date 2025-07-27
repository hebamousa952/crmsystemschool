<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SecureDataInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // تسجيل محاولات الوصول للبيانات الحساسة
        $this->logSensitiveDataAccess($request);

        // تنظيف البيانات المدخلة
        $this->sanitizeInput($request);

        // فحص محاولات الحقن
        $this->detectInjectionAttempts($request);

        return $next($request);
    }

    /**
     * Log access to sensitive data
     */
    protected function logSensitiveDataAccess(Request $request)
    {
        $sensitiveFields = [
            'national_id', 'password', 'guardian_details',
            'amount', 'payment_date', 'discount_amount'
        ];

        $inputFields = array_keys($request->all());
        $accessedSensitive = array_intersect($inputFields, $sensitiveFields);

        if (!empty($accessedSensitive)) {
            Log::info('Sensitive data access', [
                'user_id' => auth()->id(),
                'ip' => $request->ip(),
                'fields' => $accessedSensitive,
                'route' => $request->route()?->getName(),
                'timestamp' => now()
            ]);
        }
    }

    /**
     * Sanitize input data
     */
    protected function sanitizeInput(Request $request)
    {
        $input = $request->all();
        $sanitized = $this->recursiveSanitize($input);
        $request->replace($sanitized);
    }

    /**
     * Recursively sanitize array data
     */
    protected function recursiveSanitize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'recursiveSanitize'], $data);
        }

        if (is_string($data)) {
            // إزالة العلامات الخطيرة
            $data = strip_tags($data);
            // إزالة المسافات الزائدة
            $data = trim($data);
            // تحويل الأحرف الخاصة
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }

        return $data;
    }

    /**
     * Detect potential injection attempts
     */
    protected function detectInjectionAttempts(Request $request)
    {
        $suspiciousPatterns = [
            '/(<script|<\/script>)/i',
            '/(union|select|insert|update|delete|drop|create|alter)/i',
            '/(javascript:|vbscript:|onload=|onerror=)/i',
            '/(\<\?php|\?\>)/i'
        ];

        $input = json_encode($request->all());

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                Log::warning('Potential injection attempt detected', [
                    'user_id' => auth()->id(),
                    'ip' => $request->ip(),
                    'pattern' => $pattern,
                    'input_sample' => substr($input, 0, 200),
                    'route' => $request->route()?->getName(),
                    'timestamp' => now()
                ]);

                // يمكن إضافة منع الطلب هنا إذا لزم الأمر
                // abort(403, 'Suspicious input detected');
            }
        }
    }
}
