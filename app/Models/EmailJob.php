<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmailJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'email_type',
        'priority',
        'status',
        'user_id',
        'recipient_email',
        'recipient_name',
        'subject',
        'email_class',
        'email_data',
        'scheduled_at',
        'sent_at',
        'failed_at',
        'attempts',
        'max_attempts',
        'notification_id',
        'track_opens',
        'track_clicks',
        'opened_at',
        'clicked_at',
        'click_count',
        'error_message',
        'error_details',
        'smtp_provider',
        'campaign_id',
        'batch_id',
        'processing_time_ms',
        'queue_wait_time_ms'
    ];

    protected $casts = [
        'email_data' => 'array',
        'error_details' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'track_opens' => 'boolean',
        'track_clicks' => 'boolean'
    ];

    // ==================== العلاقات ====================

    /**
     * Get the user who should receive this email
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related notification
     */
    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    // ==================== Scopes ====================

    /**
     * Scope for pending emails
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for failed emails
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for sent emails
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope for scheduled emails
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')
                    ->where('scheduled_at', '>', now());
    }

    /**
     * Scope for ready to send emails
     */
    public function scopeReadyToSend($query)
    {
        return $query->where('status', 'pending')
                    ->where(function ($q) {
                        $q->whereNull('scheduled_at')
                          ->orWhere('scheduled_at', '<=', now());
                    });
    }

    /**
     * Scope for specific email type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('email_type', $type);
    }

    /**
     * Scope for specific priority
     */
    public function scopePriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope for campaign emails
     */
    public function scopeCampaign($query, $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    // ==================== Accessors ====================

    /**
     * Get formatted status
     */
    public function getFormattedStatusAttribute()
    {
        $statuses = [
            'pending' => 'في الانتظار',
            'processing' => 'قيد المعالجة',
            'sent' => 'تم الإرسال',
            'failed' => 'فشل',
            'cancelled' => 'ملغي'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Get formatted priority
     */
    public function getFormattedPriorityAttribute()
    {
        $priorities = [
            'low' => 'منخفض',
            'normal' => 'عادي',
            'high' => 'عالي',
            'urgent' => 'عاجل'
        ];

        return $priorities[$this->priority] ?? $this->priority;
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'processing' => 'info',
            'sent' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary'
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    /**
     * Get priority color for UI
     */
    public function getPriorityColorAttribute()
    {
        $colors = [
            'low' => 'secondary',
            'normal' => 'primary',
            'high' => 'warning',
            'urgent' => 'danger'
        ];

        return $colors[$this->priority] ?? 'primary';
    }

    /**
     * Check if email was opened
     */
    public function getIsOpenedAttribute()
    {
        return !is_null($this->opened_at);
    }

    /**
     * Check if email was clicked
     */
    public function getIsClickedAttribute()
    {
        return !is_null($this->clicked_at);
    }

    /**
     * Get time since sent
     */
    public function getTimeSinceSentAttribute()
    {
        return $this->sent_at ? $this->sent_at->diffForHumans() : null;
    }

    /**
     * Get processing time in seconds
     */
    public function getProcessingTimeSecondsAttribute()
    {
        return $this->processing_time_ms ? round($this->processing_time_ms / 1000, 2) : null;
    }

    // ==================== Methods ====================

    /**
     * Mark as sent
     */
    public function markAsSent($processingTime = null, $smtpProvider = null)
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
            'processing_time_ms' => $processingTime,
            'smtp_provider' => $smtpProvider
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed($errorMessage, $errorDetails = null)
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'attempts' => $this->attempts + 1,
            'error_message' => $errorMessage,
            'error_details' => $errorDetails
        ]);
    }

    /**
     * Mark as opened
     */
    public function markAsOpened()
    {
        if (!$this->opened_at) {
            $this->update(['opened_at' => now()]);
        }
    }

    /**
     * Mark as clicked
     */
    public function markAsClicked()
    {
        $this->update([
            'clicked_at' => now(),
            'click_count' => $this->click_count + 1
        ]);
    }

    /**
     * Check if can retry
     */
    public function canRetry()
    {
        return $this->status === 'failed' && $this->attempts < $this->max_attempts;
    }

    /**
     * Retry the email
     */
    public function retry()
    {
        if ($this->canRetry()) {
            $this->update([
                'status' => 'pending',
                'failed_at' => null,
                'error_message' => null,
                'error_details' => null
            ]);
            return true;
        }
        return false;
    }

    // ==================== Static Methods ====================

    /**
     * Get email statistics
     */
    public static function getStatistics($startDate = null, $endDate = null)
    {
        $query = static::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return [
            'total' => $query->count(),
            'sent' => $query->where('status', 'sent')->count(),
            'pending' => $query->where('status', 'pending')->count(),
            'failed' => $query->where('status', 'failed')->count(),
            'opened' => $query->whereNotNull('opened_at')->count(),
            'clicked' => $query->whereNotNull('clicked_at')->count(),
            'by_type' => $query->groupBy('email_type')->selectRaw('email_type, count(*) as count')->pluck('count', 'email_type'),
            'by_priority' => $query->groupBy('priority')->selectRaw('priority, count(*) as count')->pluck('count', 'priority'),
        ];
    }
}
