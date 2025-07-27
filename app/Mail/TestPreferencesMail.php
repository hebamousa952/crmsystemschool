<?php

namespace App\Mail;

use App\Models\User;

class TestPreferencesMail extends BaseMail
{
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, $notificationId = null)
    {
        parent::__construct($notificationId);

        $this->user = $user;
    }

    /**
     * Get the view name for the email
     */
    protected function getViewName(): string
    {
        return 'emails.test-preferences';
    }

    /**
     * Get the subject for the email
     */
    protected function getSubject(): string
    {
        return 'اختبار تفضيلات البريد الإلكتروني - نظام إدارة المدرسة';
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $templateData = $this->getTemplateData();

        return $this->view($this->getViewName())
                    ->with(array_merge($templateData, [
                        'user' => $this->user,
                        'preferences' => $this->user->email_preferences,
                        'priority' => 'low'
                    ]))
                    ->subject($this->getSubject());
    }
}
