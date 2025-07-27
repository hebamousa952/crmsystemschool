<?php

namespace App\Mail;

class NewsletterMail extends BaseMail
{
    public $content;
    public $edition;

    /**
     * Create a new message instance.
     */
    public function __construct($content, $edition = null, $notificationId = null)
    {
        parent::__construct($notificationId);

        $this->content = $content;
        $this->edition = $edition ?? 'العدد ' . date('Y-m');
    }

    /**
     * Get the view name for the email
     */
    protected function getViewName(): string
    {
        return 'emails.newsletter';
    }

    /**
     * Get the subject for the email
     */
    protected function getSubject(): string
    {
        return "النشرة الإخبارية - {$this->edition}";
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $templateData = $this->getTemplateData();

        return $this->view($this->getViewName())
                    ->with(array_merge($templateData, [
                        'content' => $this->content,
                        'edition' => $this->edition,
                        'priority' => 'low'
                    ]))
                    ->subject($this->getSubject());
    }
}
