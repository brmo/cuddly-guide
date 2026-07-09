<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'preferred_notification_method',
        'slack_user_id',
        'phone_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $appends = ['notification_display'];

    public function rfcsCreated(): BelongsToMany
    {
        return $this->belongsToMany(Rfc::class, 'rfc_creators', 'user_id', 'rfc_id');
    }

    public function rfcsPerforming(): BelongsToMany
    {
        return $this->belongsToMany(Rfc::class, 'rfc_performers', 'user_id', 'rfc_id');
    }

    public function rfcsStakeholders(): BelongsToMany
    {
        return $this->belongsToMany(Rfc::class, 'rfc_stakeholders', 'user_id', 'rfc_id');
    }

    public function approvals(): BelongsToMany
    {
        return $this->belongsToMany(Rfc::class, 'rfc_approvers', 'user_id', 'rfc_id')->withPivot('type', 'status', 'comments', 'reminder_minutes');
    }

    public function audits()
    {
        return $this->hasMany(AuditLog::class);
    }

    public function getNotificationDisplayAttribute(): string
    {
        return match ($this->preferred_notification_method) {
            'slack' => 'Slack',
            'email' => 'Email',
            'sms' => 'SMS',
            default => $this->preferred_notification_method ?? 'email',
        };
    }
}
