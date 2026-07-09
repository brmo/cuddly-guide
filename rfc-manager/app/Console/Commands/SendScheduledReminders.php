<?php

namespace App\Console\Commands;

use App\Jobs\SendRfcReminder;
use App\Jobs\SendStakeholderReminder;
use App\Models\Rfc;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendScheduledReminders extends Command
{
    protected $signature = 'rfcs:send-reminders';
    protected $description = 'Send scheduled reminders for RFCs';

    public function handle(): int
    {
        $now = now();

        $this->info('Processing pending RFCs...');

        $pending = Rfc::where('status', Rfc::STATUS_PENDING_APPROVAL)
            ->whereNotNull('scheduled_at')
            ->orWhere('status', Rfc::STATUS_APPROVED)
            ->orWhere('status', Rfc::STATUS_SCHEDULED)
            ->get();

        foreach ($pending as $rfc) {
            $this->info("Processing RFC #{$rfc->id}: {$rfc->subject}");

            $this->info("  Current status: {$rfc->status}");
            $this->info("  Scheduled at: " . ($rfc->scheduled_at?->format('Y-m-d H:i') ?? 'N/A'));

            if ($rfc->status === Rfc::STATUS_SCHEDULED) {
                $scheduled = $rfc->scheduled_at;

                if ($scheduled) {
                    $diffHours = $scheduled->diffInHours($now);

                    if ($diffHours <= 24 && $diffHours > 0) {
                        $this->info("  Stakeholders notified (within 24h)");
                        foreach ($rfc->stakeholders as $stakeholder) {
                            $stakeholder->notify(new \App\Notifications\RfcScheduled($rfc));
                        }
                    } elseif ($diffHours > 24) {
                        $this->info("  Scheduling 12h-before reminder...");
                        SendStakeholderReminder::dispatch($rfc, $rfc->creator, 12)
                            ->delay($scheduled->subHours(12));
                    }
                }

                if ($scheduled && $scheduled->isPast() && $rfc->status !== Rfc::STATUS_IN_PROGRESS) {
                    $rfc->update(['status' => Rfc::STATUS_IN_PROGRESS, 'change_started_at' => $scheduled]);
                }

                foreach ($rfc->performers as $performer) {
                    if ($performer->pivot->reminder_minutes > 0 && $scheduled) {
                        $this->info("  Scheduling performer reminder for {$performer->name}");
                        SendRfcReminder::dispatch($rfc, $rfc->creator, $performer, $performer->pivot->reminder_minutes)
                            ->delay($scheduled->subMinutes($performer->pivot->reminder_minutes));
                    }
                }
            }

            if ($rfc->status === Rfc::STATUS_PENDING_APPROVAL) {
                foreach ($rfc->approvers as $approver) {
                    if ($approver->pivot->status === 'pending') {
                        $this->info("  Re-sending approval request to {$approver->name}");
                        $approver->notify(new \App\Notifications\RfcSubmitted($rfc, $approver));
                    }
                }
            }
        }

        $this->info('Reminders sent successfully.');
        return 0;
    }
}
