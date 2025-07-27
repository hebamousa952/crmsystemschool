<?php

namespace App\Mail;

class BulkNotificationMail extends BaseMail
{
    public $message;
    public $sender;
    public $targetInfo;

    /**
     * Create a new message instance.
     */
    public function __construct($message, $sender, $targetInfo = null, $notificationId = null)
    {
        parent::__construct($notificationId);

        $this->message = $message;
        $this->sender = $sender;
        $this->targetInfo = $targetInfo;
    }

    /**
     * Get the view name for the email
     */
    protected function getViewName(): string
    {
        return 'emails.bulk-notification';
    }

    /**
     * Get the subject for the email
     */
    protected function getSubject(): string
    {
        return $this->subject ?? 'إشعار جماعي من إدارة المدرسة';
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
                        'sender' => $this->sender,
                        'targetInfo' => $this->targetInfo,
                        'priority' => 'normal'
                    ]))
                    ->subject($this->getSubject());
    }
}
