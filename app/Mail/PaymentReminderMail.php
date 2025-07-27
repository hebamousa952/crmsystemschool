<?php

namespace App\Mail;

use App\Models\Student;

class PaymentReminderMail extends BaseMail
{
    public $students;
    public $reminderType;

    /**
     * Create a new message instance.
     */
    public function __construct($students, $reminderType = 'gentle', $notificationId = null)
    {
        parent::__construct($notificationId);

        $this->students = is_array($students) ? collect($students) : $students;
        $this->reminderType = $reminderType;
    }

    /**
     * Get the view name for the email
     */
    protected function getViewName(): string
    {
        return 'emails.payment-reminder';
    }

    /**
     * Get the subject for the email
     */
    protected function getSubject(): string
    {
        $types = [
            'gentle' => 'تذكير ودي بالمصروفات المدرسية',
            'urgent' => 'تذكير عاجل بالمصروفات المستحقة',
            'final' => 'إشعار نهائي - المصروفات المستحقة'
        ];

        return $types[$this->reminderType] ?? 'تذكير بالمصروفات المدرسية';
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $templateData = $this->getTemplateData();

        $totalOutstanding = $this->students->sum('remaining_amount');

        return $this->view($this->getViewName())
                    ->with(array_merge($templateData, [
                        'students' => $this->students,
                        'reminderType' => $this->reminderType,
                        'totalOutstanding' => $totalOutstanding,
                        'priority' => $this->reminderType === 'final' ? 'urgent' : 'high'
                    ]))
                    ->subject($this->getSubject());
    }
}
