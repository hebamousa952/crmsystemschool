<?php

namespace App\Mail;

use App\Models\Student;
use App\Models\User;

class StudentCreatedMail extends BaseMail
{
    public $student;
    public $createdBy;
    public $activityLog;

    /**
     * Create a new message instance.
     */
    public function __construct(Student $student, User $createdBy, $activityLog = null, $notificationId = null)
    {
        parent::__construct($notificationId);

        $this->student = $student;
        $this->createdBy = $createdBy;
        $this->activityLog = $activityLog;
    }

    /**
     * Get the view name for the email
     */
    protected function getViewName(): string
    {
        return 'emails.student-created';
    }

    /**
     * Get the subject for the email
     */
    protected function getSubject(): string
    {
        return "تم تسجيل طالب جديد: {$this->student->full_name_ar}";
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $templateData = $this->getTemplateData();

        return $this->view($this->getViewName())
                    ->with(array_merge($templateData, [
                        'student' => $this->student,
                        'createdBy' => $this->createdBy,
                        'activityLog' => $this->activityLog,
                        'studentData' => $this->getStudentData(),
                        'actionUrl' => url('/students/' . $this->student->id),
                        'priority' => 'normal'
                    ]))
                    ->subject($this->getSubject());
    }

    /**
     * Get formatted student data for email
     */
    private function getStudentData()
    {
        return [
            'basic_info' => [
                'الاسم الكامل' => $this->student->full_name_ar,
                'الرقم القومي' => $this->student->national_id,
                'تاريخ الميلاد' => $this->formatDate($this->student->birth_date),
                'العمر' => $this->student->age . ' سنة',
                'الجنس' => $this->student->gender === 'male' ? 'ذكر' : 'أنثى'
            ],
            'academic_info' => [
                'المرحلة الدراسية' => $this->student->grade ? $this->student->grade->grade_name : 'غير محدد',
                'الفصل' => $this->student->classroom ? $this->student->classroom->full_name : 'غير محدد',
                'السنة الدراسية' => $this->student->academic_year,
                'تاريخ التسجيل' => $this->formatDate($this->student->enrollment_date),
                'الحالة' => $this->student->status_in_arabic
            ],
            'contact_info' => [
                'العنوان' => $this->student->address ?: 'غير محدد',
                'رقم الهاتف' => $this->student->phone ? $this->formatPhone($this->student->phone) : 'غير محدد'
            ],
            'financial_info' => [
                'إجمالي المصروفات' => $this->formatCurrency($this->student->total_fees ?: 0),
                'المبلغ المدفوع' => $this->formatCurrency($this->student->total_paid ?: 0),
                'المبلغ المتبقي' => $this->formatCurrency($this->student->remaining_amount ?: 0),
                'نسبة السداد' => ($this->student->payment_percentage ?: 0) . '%'
            ],
            'system_info' => [
                'تم التسجيل بواسطة' => $this->createdBy->name,
                'دور المستخدم' => $this->createdBy->formatted_role,
                'وقت التسجيل' => $this->formatDate($this->student->created_at, 'd/m/Y H:i'),
                'رقم الطالب في النظام' => $this->student->id
            ]
        ];
    }
}
