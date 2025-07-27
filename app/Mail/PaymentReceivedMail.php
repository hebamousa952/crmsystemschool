<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\User;

class PaymentReceivedMail extends BaseMail
{
    public $payment;
    public $receivedBy;
    public $activityLog;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment, User $receivedBy, $activityLog = null, $notificationId = null)
    {
        parent::__construct($notificationId);

        $this->payment = $payment;
        $this->receivedBy = $receivedBy;
        $this->activityLog = $activityLog;
    }

    /**
     * Get the view name for the email
     */
    protected function getViewName(): string
    {
        return 'emails.payment-received';
    }

    /**
     * Get the subject for the email
     */
    protected function getSubject(): string
    {
        return "تم استلام دفعة بمبلغ {$this->formatCurrency($this->payment->amount)} - {$this->payment->student->full_name_ar}";
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $templateData = $this->getTemplateData();

        return $this->view($this->getViewName())
                    ->with(array_merge($templateData, [
                        'payment' => $this->payment,
                        'receivedBy' => $this->receivedBy,
                        'activityLog' => $this->activityLog,
                        'paymentData' => $this->getPaymentData(),
                        'receiptUrl' => url('/payments/' . $this->payment->id . '/receipt'),
                        'studentUrl' => url('/students/' . $this->payment->student_id),
                        'priority' => 'normal'
                    ]))
                    ->subject($this->getSubject());
    }

    /**
     * Get formatted payment data for email
     */
    private function getPaymentData()
    {
        $student = $this->payment->student;

        return [
            'payment_info' => [
                'رقم الإيصال' => $this->payment->receipt_number ?: 'سيتم إنشاؤه تلقائياً',
                'المبلغ المدفوع' => $this->formatCurrency($this->payment->amount),
                'طريقة الدفع' => $this->payment->method_in_arabic,
                'تاريخ الدفع' => $this->formatDate($this->payment->payment_date),
                'حالة الدفعة' => $this->payment->status_in_arabic,
                'ملاحظات' => $this->payment->notes ?: 'لا توجد ملاحظات'
            ],
            'student_info' => [
                'اسم الطالب' => $student->full_name_ar,
                'الرقم القومي' => $student->national_id,
                'المرحلة الدراسية' => $student->grade ? $student->grade->grade_name : 'غير محدد',
                'الفصل' => $student->classroom ? $student->classroom->full_name : 'غير محدد',
                'رقم الطالب' => $student->id
            ],
            'financial_summary' => [
                'إجمالي المصروفات' => $this->formatCurrency($student->total_fees ?: 0),
                'إجمالي المدفوع' => $this->formatCurrency($student->total_paid ?: 0),
                'المبلغ المتبقي' => $this->formatCurrency($student->remaining_amount ?: 0),
                'نسبة السداد' => ($student->payment_percentage ?: 0) . '%',
                'حالة السداد' => $this->getPaymentStatus($student)
            ],
            'transaction_info' => [
                'تم الاستلام بواسطة' => $this->receivedBy->name,
                'دور المستخدم' => $this->receivedBy->formatted_role,
                'وقت الاستلام' => $this->formatDate($this->payment->created_at, 'd/m/Y H:i'),
                'رقم المعاملة' => $this->payment->id
            ]
        ];
    }

    /**
     * Get payment status description
     */
    private function getPaymentStatus($student)
    {
        $percentage = $student->payment_percentage ?: 0;

        if ($percentage >= 100) {
            return 'مدفوع بالكامل ✅';
        } elseif ($percentage >= 50) {
            return 'مدفوع جزئياً 🟡';
        } elseif ($percentage > 0) {
            return 'دفعة أولى 🟠';
        } else {
            return 'غير مدفوع ❌';
        }
    }
}
