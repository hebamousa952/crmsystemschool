<?php

namespace App\Mail;

use App\Models\Student;

class GradeReportMail extends BaseMail
{
    public $student;
    public $reportPeriod;
    public $grades;

    /**
     * Create a new message instance.
     */
    public function __construct(Student $student, $grades = [], $reportPeriod = null, $notificationId = null)
    {
        parent::__construct($notificationId);

        $this->student = $student;
        $this->grades = $grades;
        $this->reportPeriod = $reportPeriod ?? 'الفصل الدراسي الحالي';
    }

    /**
     * Get the view name for the email
     */
    protected function getViewName(): string
    {
        return 'emails.grade-report';
    }

    /**
     * Get the subject for the email
     */
    protected function getSubject(): string
    {
        return "تقرير درجات الطالب {$this->student->full_name_ar} - {$this->reportPeriod}";
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $templateData = $this->getTemplateData();

        $averageGrade = collect($this->grades)->avg('grade') ?? 0;
        $totalSubjects = count($this->grades);

        return $this->view($this->getViewName())
                    ->with(array_merge($templateData, [
                        'student' => $this->student,
                        'grades' => $this->grades,
                        'reportPeriod' => $this->reportPeriod,
                        'averageGrade' => round($averageGrade, 2),
                        'totalSubjects' => $totalSubjects,
                        'priority' => 'normal'
                    ]))
                    ->subject($this->getSubject());
    }
}
