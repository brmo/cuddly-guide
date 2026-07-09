<?php

namespace App\Notifications;

use App\Models\Rfc;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class RfcFullyApproved extends Notification implements ShouldQueue
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
        $url = url('/rfcs/' . $this->rfc->id . '/schedule');

        return (new MailMessage)
            ->subject('RFC Fully Approved: ' . $this->rfc->subject)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Great news! All mandatory approvers have approved the following RFC.')
            ->line('Subject: ' . $this->rfc->subject)
            ->line('You may now schedule the change.')
            ->action('Schedule Change', $url);
    }

    public function toSlack(\App\Models\User $notifiable): SlackMessage
    {
        $url = url('/rfcs/' . $this->rfc->id . '/schedule');

        return (new SlackMessage)
            ->to($notifiable->slack_user_id)
            ->content('RFC "' . $this->rfc->subject . '" has been fully approved!')
            ->attachment(function ($attachment) use ($url) {
                $attachment->title('Schedule Change', $url);
            });
    }

    public function toArray(\App\Models\User $notifiable): array
    {
        return [
            'rfc_id' => $this->rfc->id,
            'subject' => $this->rfc->subject,
            'action' => 'fully_approved',
        ];
    }
}
