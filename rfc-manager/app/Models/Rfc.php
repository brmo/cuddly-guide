<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rfc extends Model
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_SCHEDULED = 'scheduled';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REJECTED = 'rejected';
    const STATUS_RECALLED = 'recalled';

    const SEVERITY_LOW = 'low';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_CRITICAL = 'critical';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';

    protected $fillable = [
        'subject',
        'description',
        'status',
        'severity',
        'downtime_possible',
        'priority',
        'scheduled_at',
        'change_started_at',
        'change_completed_at',
        'rejection_reason',
        'recurrent_reminder_enabled',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'change_started_at' => 'datetime',
        'change_completed_at' => 'datetime',
        'downtime_possible' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function performers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'rfc_performers')->withPivot('reminder_minutes')->withTimestamps();
    }

    public function stakeholders(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'rfc_stakeholders')->withTimestamps();
    }

    public function approvers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'rfc_approvers')->withPivot('type', 'status', 'comments', 'reminder_minutes')->withTimestamps();
    }

    public function audits(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING_APPROVAL);
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_RECALLED => 'Recalled',
        ];
    }

    public static function getSeverityOptions(): array
    {
        return [
            self::SEVERITY_LOW => 'Low',
            self::SEVERITY_MEDIUM => 'Medium',
            self::SEVERITY_HIGH => 'High',
            self::SEVERITY_CRITICAL => 'Critical',
        ];
    }

    public static function getPriorityOptions(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_CRITICAL => 'Critical',
        ];
    }

    public function isRecalled(): bool
    {
        return $this->status === self::STATUS_RECALLED;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function isScheduled(): bool
    {
        return in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_IN_PROGRESS]);
    }

    public function getMandatoryApprovers()
    {
        return $this->approvers()->wherePivot('type', 'mandatory')->get();
    }

    public function getOptionalApprovers()
    {
        return $this->approvers()->wherePivot('type', 'optional')->get();
    }

    public function getBackupApprovers()
    {
        return $this->approvers()->wherePivot('type', 'backup')->get();
    }

    public function isFullyApproved(): bool
    {
        $mandatory = $this->approvers()->wherePivot('type', 'mandatory')->count();
        $mandatoryApproved = $this->approvers()
            ->wherePivot('type', 'mandatory')
            ->wherePivot('status', 'approved')
            ->count();

        return $mandatory === 0 || $mandatoryApproved >= $mandatory;
    }
}
