<?php

namespace App\Notifications;

use App\Models\Rfc;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class RfcCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Rfc $rfc)
    {
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

        return (new MailMessage)
            ->subject('Change Completed: ' . $this->rfc->subject)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The change has been successfully completed.')
            ->line('Subject: ' . $this->rfc->subject)
            ->action('View RFC', $url);
    }

    public function toSlack(\App\Models\User $notifiable): SlackMessage
    {
        $url = url('/rfcs/' . $this->rfc->id);

        return (new SlackMessage)
            ->to($notifiable->slack_user_id)
            ->content('Change completed: *' . $this->rfc->subject . '*')
            ->attachment(function ($attachment) use ($url) {
                $attachment->title('View RFC', $url);
            });
    }

    public function toArray(\App\Models\User $notifiable): array
    {
        return [
            'rfc_id' => $this->rfc->id,
            'subject' => $this->rfc->subject,
            'action' => 'completed',
        ];
    }
}
