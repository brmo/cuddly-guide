<?php

namespace App\Notifications;

use App\Models\Rfc;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class RfcApprovalUpdate extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Rfc $rfc,
        public string $status,
        public ?User $approver = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        $channels = [];

        if ($notifiable->preferred_notification_method === 'email') {
            $channels[] = 'mail';
        } elseif ($notifiable->preferred_notification_method === 'slack') {
            $channels[] = 'slack';
        }

        $channels[] = 'database';

        return $channels;
    }

    public function toMail(\App\Models\User $notifiable): MailMessage
    {
        $url = url('/rfcs/' . $this->rfc->id);
        $statusLabel = match ($this->status) {
            'approved' => 'approved',
            'rejected' => 'rejected',
            default => ucfirst($this->status),
        };

        $summary = match ($this->status) {
            'approved' => 'An approver has approved the following RFC.',
            'rejected' => 'An approver has rejected the following RFC.',
            default => 'An update has been made to the following RFC.',
        };

        return (new MailMessage)
            ->subject('RFC ' . ucfirst($this->status) . ': ' . $this->rfc->subject)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($summary)
            ->line('Subject: ' . $this->rfc->subject)
            ->when($this->approver, function ($message) {
                $message->line('By: ' . $this->approver->name);
            })
            ->line('Status: ' . $statusLabel)
            ->action('View RFC', $url);
    }

    public function toSlack(\App\Models\User $notifiable): SlackMessage
    {
        $url = url('/rfcs/' . $this->rfc->id);

        return (new SlackMessage)
            ->to($notifiable->slack_user_id)
            ->content('RFC "' . $this->rfc->subject . '" has been ' . ucfirst($this->status) . '.')
            ->attachment(function ($attachment) use ($url) {
                $attachment->title('View RFC', $url);
            });
    }

    public function toArray(\App\Models\User $notifiable): array
    {
        return [
            'rfc_id' => $this->rfc->id,
            'subject' => $this->rfc->subject,
            'status' => $this->rfc->status,
            'action' => $this->status,
        ];
    }
}
