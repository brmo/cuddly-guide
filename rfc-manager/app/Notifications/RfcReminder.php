<?php

namespace App\Notifications;

use App\Models\Rfc;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

class RfcReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Rfc $rfc,
        public ?User $notifiedBy = null,
        public ?int $minutesBefore = null,
    ) {
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
        $minutes = $this->minutesBefore ?? 30;

        return (new MailMessage)
            ->subject('RFC Reminder: ' . $this->rfc->subject . ' in ' . $minutes . ' minutes')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('This is a reminder that the following change is scheduled to take place in approximately ' . $minutes . ' minutes.')
            ->line('Subject: ' . $this->rfc->subject)
            ->when($this->notifiedBy, function ($message) {
                $message->line('Setup by: ' . $this->notifiedBy->name);
            })
            ->action('View RFC Details', $url);
    }

    public function toSlack(\App\Models\User $notifiable): SlackMessage
    {
        $url = url('/rfcs/' . $this->rfc->id);
        $minutes = $this->minutesBefore ?? 30;

        return (new SlackMessage)
            ->to($notifiable->slack_user_id)
            ->content('Reminder: RFC "' . $this->rfc->subject . '" starts in ' . $minutes . ' minutes!')
            ->attachment(function ($attachment) use ($url) {
                $attachment->title('View RFC', $url);
            });
    }

    public function toArray(\App\Models\User $notifiable): array
    {
        return [
            'rfc_id' => $this->rfc->id,
            'subject' => $this->rfc->subject,
            'action' => 'reminder',
            'minutes_before' => $this->minutesBefore,
        ];
    }
}
