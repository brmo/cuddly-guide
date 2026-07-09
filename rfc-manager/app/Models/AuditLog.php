<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    const ACTION_CREATED = 'created';
    const ACTION_UPDATED = 'updated';
    const ACTION_SUBMITTED = 'submitted';
    const ACTION_APPROVED = 'approved';
    const ACTION_REJECTED = 'rejected';
    const ACTION_RECALLED = 'recalled';
    const ACTION_RESUBMITTED = 'resubmitted';
    const ACTION_SCHEDULED = 'scheduled';
    const ACTION_STARTED = 'started';
    const ACTION_COMPLETED = 'completed';
    const ACTION_NOTIFICATION_SENT = 'notification_sent';
    const ACTION_ATTACHMENT_ADDED = 'attachment_added';

    protected $fillable = [
        'rfc_id',
        'user_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public static function getActions(): array
    {
        return [
            self::ACTION_CREATED,
            self::ACTION_UPDATED,
            self::ACTION_SUBMITTED,
            self::ACTION_APPROVED,
            self::ACTION_REJECTED,
            self::ACTION_RECALLED,
            self::ACTION_RESUBMITTED,
            self::ACTION_SCHEDULED,
            self::ACTION_STARTED,
            self::ACTION_COMPLETED,
            self::ACTION_NOTIFICATION_SENT,
            self::ACTION_ATTACHMENT_ADDED,
        ];
    }

    public function rfc(): BelongsTo
    {
        return $this->belongsTo(Rfc::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
