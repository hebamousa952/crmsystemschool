<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Services\MailConfigService;

abstract class BaseMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $notificationId;
    protected $schoolInfo;
    protected $trackingEnabled;

    /**
     * Create a new message instance.
     */
    public function __construct($notificationId = null)
    {
        $this->notificationId = $notificationId;
        $this->schoolInfo = MailConfigService::getSchoolInfo();
        $this->trackingEnabled = MailConfigService::getTrackingSettings()['enabled'];

        // Set queue
        $this->onQueue('emails');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $trackingPixel = null;

        if ($this->trackingEnabled && $this->notificationId) {
            $trackingPixel = MailConfigService::generateTrackingPixel($this->notificationId);
        }

        return $this->view($this->getViewName())
                    ->with([
                        'schoolInfo' => $this->schoolInfo,
                        'trackingPixel' => $trackingPixel,
                        'notificationId' => $this->notificationId
                    ])
                    ->subject($this->getSubject());
    }

    /**
     * Get the view name for the email
     */
    abstract protected function getViewName(): string;

    /**
     * Get the subject for the email
     */
    abstract protected function getSubject(): string;

    /**
     * Format currency for display
     */
    protected function formatCurrency($amount)
    {
        return number_format($amount, 2) . ' جنيه';
    }

    /**
     * Format date for display
     */
    protected function formatDate($date, $format = 'd/m/Y')
    {
        if (is_string($date)) {
            $date = \Carbon\Carbon::parse($date);
        }
        return $date->format($format);
    }

    /**
     * Get template data
     */
    protected function getTemplateData()
    {
        return [
            'schoolInfo' => $this->schoolInfo,
            'trackingPixel' => $this->trackingEnabled && $this->notificationId
                ? MailConfigService::generateTrackingPixel($this->notificationId)
                : null,
            'notificationId' => $this->notificationId,
            'timestamp' => now()->format('d/m/Y H:i'),
            'year' => date('Y')
        ];
    }
}
