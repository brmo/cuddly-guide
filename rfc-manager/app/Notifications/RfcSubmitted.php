<?php

namespace App\Notifications;

use App\Models\Rfc;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;

class RfcSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Rfc $rfc, public \App\Models\User $approver)
    {
    }

    public function via(object $notifiable): array
    {
        $method = $notifiable->preferred_notification_method;
        $channels = [];

        if ($method === 'email') {
            $channels[] = 'mail';
        } elseif ($method === 'slack') {
            $channels[] = 'slack';
        }

        $channels[] = 'database';

        return $channels;
    }

    public function toMail(\App\Models\User $notifiable): MailMessage
    {
        $url = url('/rfcs/' . $this->rfc->id);

        return (new MailMessage)
            ->subject('RFC Pending Approval: ' . $this->rfc->subject)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A new RFC has been submitted and requires your approval.')
            ->line('Subject: ' . $this->rfc->subject)
            ->line('Severity: ' . ucfirst($this->rfc->severity))
            ->line('Priority: ' . ucfirst($this->rfc->priority))
            ->action('Review RFC', $url)
            ->line('Submitted by: ' . $this->rfc->creator->name);
    }

    public function toSlack(\App\Models\User $notifiable): SlackMessage
    {
        $url = url('/rfcs/' . $this->rfc->id);

        return (new SlackMessage)
            ->to($notifiable->slack_user_id)
            ->content('A new RFC requires your approval: *' . $this->rfc->subject . '*')
            ->attachment(function ($attachment) use ($url) {
                $attachment->title('Review RFC', $url);
            });
    }

    public function toArray(\App\Models\User $notifiable): array
    {
        return [
            'rfc_id' => $this->rfc->id,
            'subject' => $this->rfc->subject,
            'status' => $this->rfc->status,
            'action' => 'submitted',
        ];
    }
}
