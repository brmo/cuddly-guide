<?php

namespace App\Notifications;

use App\Models\Rfc;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;

class RfcScheduled extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Rfc $rfc)
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
            ->subject('Scheduled Change: ' . $this->rfc->subject)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A change has been scheduled and you have been notified as a stakeholder.')
            ->line('Subject: ' . $this->rfc->subject)
            ->line('Description: ' . \Illuminate\Support\Str::limit(strip_tags($this->rfc->description), 200))
            ->when($this->rfc->scheduled_at, function ($message) {
                $message->line('Scheduled: ' . $this->rfc->scheduled_at->format('Y-m-d H:i'));
            })
            ->action('View RFC', $url);
    }

    public function toSlack(\App\Models\User $notifiable): SlackMessage
    {
        $url = url('/rfcs/' . $this->rfc->id);

        return (new SlackMessage)
            ->to($notifiable->slack_user_id)
            ->content('A change has been scheduled: *' . $this->rfc->subject . '*')
            ->attachment(function ($attachment) use ($url) {
                $attachment->title('View RFC', $url);
            });
    }

    public function toArray(\App\Models\User $notifiable): array
    {
        return [
            'rfc_id' => $this->rfc->id,
            'subject' => $this->rfc->subject,
            'action' => 'scheduled',
        ];
    }
}
