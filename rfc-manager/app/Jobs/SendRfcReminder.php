<?php

namespace App\Jobs;

use App\Models\Rfc;
use App\Models\User;
use App\Notifications\RfcReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRfcReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Rfc $rfc,
        public ?User $notifiedBy = null,
        public User $recipient,
        public ?int $minutesBefore = 30,
    ) {
    }

    public function handle(): void
    {
        $this->recipient->notify(new RfcReminder($this->rfc, $this->notifiedBy, $this->minutesBefore));
    }
}
