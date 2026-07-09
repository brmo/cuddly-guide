<?php

namespace App\Jobs;

use App\Models\Rfc;
use App\Notifications\RfcReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendStakeholderReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Rfc $rfc,
        public ?\App\Models\User $notifiedBy = null,
        public int $hoursBefore = 12,
    ) {
    }

    public function handle(): void
    {
        foreach ($this->rfc->stakeholders as $stakeholder) {
            $stakeholder->notify(new RfcReminder($this->rfc, $this->notifiedBy, $this->hoursBefore * 60));
        }
    }
}
