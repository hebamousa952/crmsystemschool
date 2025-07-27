<?php

namespace App\Mail;

class EmergencyNotificationMail extends BaseMail
{
    public $message;
    public $emergencyLevel;

    /**
     * Create a new message instance.
     */
    public function __construct($message, $emergencyLevel = 'high', $notificationId = null)
    {
        parent::__construct($notificationId);

        $this->message = $message;
        $this->emergencyLevel = $emergencyLevel;
    }

    /**
     * Get the view name for the email
     */
    protected function getViewName(): string
    {
        return 'emails.emergency-notification';
    }

    /**
     * Get the subject for the email
     */
    protected function getSubject(): string
    {
        $levels = [
            'urgent' => '🚨 إشعار طارئ عاجل',
            'high' => '⚠️ إشعار مهم',
            'medium' => '📢 إشعار'
        ];

        return $levels[$this->emergencyLevel] ?? '📢 إشعار من إدارة المدرسة';
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $templateData = $this->getTemplateData();

        return $this->view($this->getViewName())
                    ->with(array_merge($templateData, [
                        'message' => $this->message,
                        'emergencyLevel' => $this->emergencyLevel,
                        'priority' => 'urgent'
                    ]))
                    ->subject($this->getSubject());
    }
}
